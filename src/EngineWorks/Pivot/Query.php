<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

use EngineWorks\DBAL\DBAL;

class Query
{
    /**
     * Database Abstraction Layer Object
     * @var DBAL
     */
    private $db;

    /**
     * @var Pivot
     */
    private $pivot;

    /**
     * Query constructor.
     * @param DBAL $db
     * @param Pivot $pivot
     */
    public function __construct(DBAL $db, Pivot $pivot)
    {
        $this->db = $db;
        $this->pivot = $pivot;
    }

    /**
     * @return DBAL
     */
    public function getDb(): DBAL
    {
        return $this->db;
    }

    /**
     * @return Pivot
     */
    public function getPivot(): Pivot
    {
        return $this->pivot;
    }

    /**
     * Perform a query to obtain the distinct values of a fieldname
     *
     * @param string $fieldname
     * @return array
     * @throws PivotException if the fieldname does not exists in the source fields
     */
    public function queryDistinct(string $fieldname) : array
    {
        if (! $this->pivot->getFieldsCollection()->exists($fieldname)) {
            throw new PivotException("Field $fieldname does not exists in the source fields");
        }
        $sql = 'SELECT DISTINCT ' . $this->db->sqlFieldEscape($fieldname)
            . ' FROM ' . $this->sqlFrom()
            . ' ORDER BY ' . $this->db->sqlFieldEscape($fieldname)
            . ';';
        return $this->db->queryArrayOne($sql) ? : [];
    }

    /**
     * @return QueryResult
     */
    public function query() : QueryResult
    {
        return new QueryResult(
            $this->queryTotalsResult(true),
            ($this->pivot->hasColumns()) ? $this->queryDetailsResult() : null,
            $this->pivot->getCurrentRows(),
            $this->pivot->getCurrentColumns(),
            $this->pivot->getCurrentAggregates()
        );
    }

    /**
     * @return array
     * @throws PivotException
     * @throws PivotExceptionQuery
     */
    private function queryData() : array
    {
        $sqlSelects = [];
        // SQL: Select group fields
        foreach ($this->pivot->getCurrentRows() as $currentRow) {
            $sqlSelects[] = $this->db->sqlFieldEscape($currentRow['fieldname']);
        }
        // SQL: Select aggregator fields
        $sqlAggregates = [];
        foreach ($this->pivot->getAggregatesCollection() as $aggregate) {
            $sqlAggregates[] = $aggregate->getSQL($this->db);
        }
        // SQL: Select group fields and aggregates
        $sqlAllFields = array_merge($sqlSelects, $sqlAggregates);
        $countFields = count($sqlAllFields);
        if (! $countFields) { // nothing to select
            throw new PivotException('Nothing to query');
        }

        // get all elements from filter
        $sqlWheres = [];
        foreach ($this->pivot->getFiltersCollection() as $filter) {
            $sqlWheres[] = '('
                . $filter->getSQL($this->db, $this->pivot->getFieldElement($filter->getFieldname())->toDBAL())
                . ')';
        }
        // SQL Creation
        $sql = 'SELECT ' . implode(', ', $sqlAllFields)
            . ' FROM ' . $this->sqlFrom()
            . ((count($sqlWheres)) ? ' WHERE ' . implode(' AND ', $sqlWheres) : '')
            . ((count($sqlSelects)) ? ' GROUP BY ' . implode(', ', $sqlSelects) . ' WITH ROLLUP' : '')
            . ';';

        // retrieve and return the data
        try {
            $data = $this->db->queryArrayValues($sql);
        } catch (\Throwable $ex) {
            throw new PivotExceptionQuery('Query error: ' . $ex->getMessage());
        }
        if (! is_array($data)) {
            throw new PivotExceptionQuery('Query error:' . $sql);
        }
        return $data;
    }

    /**
     * @param bool $sortValues
     * @return Result
     * @throws PivotException
     */
    private function queryTotalsResult(bool $sortValues) : Result
    {
        // variable to return
        $result = new Result('', '');

        // now all the data including rollup cells are in $data
        // we have to generate the output
        $data = $this->queryData();
        $dataCount = count($data);
        $aggregates = $this->pivot->getAggregatesCollection();
        for ($i = 0; $i < $dataCount; $i++) {
            // create the values content
            $values = [];
            foreach ($aggregates as $aggregate) {
                $aggregateName = $aggregate->getAsname();
                $values[$aggregateName] = $data[$i][$aggregateName];
            }
            // ahora especificar donde va el resultado
            $ch = $result;
            foreach ($this->pivot->getCurrentRows() as $row) {
                $rowvalue = $data[$i][$row['fieldname']];
                if (is_null($rowvalue)) {
                    break;
                } else {
                    if (! $ch->children->exists($rowvalue)) {
                        // insert without value ($row is the $fieldname and $rowvalue is the $caption)
                        $ch->children->addItem(new Result($row['fieldname'], $rowvalue), $rowvalue);
                    }
                    $ch = $ch->children->value($rowvalue);
                }
            }
            // asignar los valores al nodo seleccionado
            if (! is_null($ch->values)) {
                throw new PivotException('Duplicated values when filling the results');
            }
            $ch->values = $values;
        }
        // do sort
        if ($sortValues) {
            $ordering = new ResultOrdering($aggregates->getOrderArray());
            if ($ordering->isRequired()) {
                $result->orderBy($ordering);
            }
        }
        // format aggregate values after sort
        $this->formatResultValues($aggregates, $result);
        return $result;
    }

    private function formatResultValues(Aggregates $aggregates, Result $result)
    {
        foreach ($aggregates as $aggregatorName => $aggregate) {
            if (null !== $value = $result->getCurrentValue($aggregatorName)) {
                $result->setCurrentValue($aggregatorName, number_format($value, $aggregate->getDecimals()));
            }
        }
        foreach ($result->children as $child) {
            $this->formatResultValues($aggregates, $child);
        }
    }

    /**
     * @return Result Null if no columns are set
     * @throws PivotException If no columns are defined
     */
    private function queryDetailsResult() : Result
    {
        $columns = $this->pivot->getCurrentColumns();
        if (! count($columns)) {
            throw new PivotException('Cannot get details if no columns are defined');
        }
        $pivotDetails = clone $this->pivot;
        $pivotDetails->clearSelectorRows();
        $pivotDetails->clearSelectorColumns();
        foreach ($columns as $column) {
            $pivotDetails->addRow($column['fieldname']);
        }
        foreach ($this->pivot->getCurrentRows() as $row) {
            $pivotDetails->addRow($row['fieldname']);
        }
        $queryClone = new self($this->db, $pivotDetails);
        $result = $queryClone->queryTotalsResult(false);
        $result->setAsNotRow(count($columns));
        return $result;
    }

    /**
     * Return the FROM part of the SQL statement
     * @return string
     */
    private function sqlFrom() : string
    {
        // do trim including ';' at the right part
        $sqlSource = rtrim(ltrim($this->pivot->getSource()), "; \t\n\r\x0B\0");
        // check if begins with select, if so then insert into a subquery
        $pos = strpos(strtoupper($sqlSource), 'SELECT');
        if ($pos === 0) {
            $sqlSource = "($sqlSource) AS __pivot__";
        } else {
            $sqlSource = $this->db->sqlTableEscape($sqlSource);
        }
        return $sqlSource;
    }
}
