<?php
namespace EngineWorks\Pivot;

use EngineWorks\DBAL\DBAL;

class Pivot
{
    /**
     * Table name, View name or SELECT instruction to extract the values
     * @var string
     */
    private $source;

    /**
     * Database Abstraction Layer Object
     * @var DBAL
     */
    private $db;

    /**
     * Collection of all fields used in the source
     * @var Fields|Field[]
     */
    private $fields;

    /**
     * Collection of rules
     * @var Filters|Filter[]
     */
    private $filters;

    /**
     * Collection of columns
     * @var array
     */
    private $columns;

    /**
     * Collection of rows
     * @var array
     */
    private $rows;

    /**
     * Collection of aggregates
     * @var Aggregates|Aggregate[]
     */
    private $aggregates;

    /**
     * Information of extended information
     * @var array $info
     */
    private $info;

    public function __construct(DBAL $db, $source = '')
    {
        $this->db = $db;
        $this->fields = new Fields();
        $this->aggregates = new Aggregates();
        $this->filters = new Filters();
        $this->rows = [];
        $this->columns = [];
        $this->info = [];
        $this->clearInfo();
        $this->setSource($source);
    }

    public function __clone()
    {
        // Force a copy of this->object, otherwise  it will point to same object.
        if (null !== $this->fields) {
            $this->fields = clone $this->fields;
        }
        if (null !== $this->filters) {
            $this->filters = clone $this->filters;
        }
        if (null !== $this->aggregates) {
            $this->aggregates = clone $this->aggregates;
        }
    }

    /**
     * @param string $source
     *
     * @throws PivotException If the source is not a string
     */
    public function setSource($source)
    {
        if (! is_string($source)) {
            throw new PivotException('A source string must be provided');
        }
        $this->source = (string) $source;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Return a current value of information
     * If keyword not found return FALSE
     * If no keyword provided then it returns the complete array of values
     *
     * @param string $keyword
     * @return mixed
     */
    public function getInfo($keyword = '')
    {
        if (! $keyword) {
            return $this->info;
        }
        if (array_key_exists($keyword, $this->info)) {
            return $this->info[$keyword];
        }
        return false;
    }

    /**
     * @param string $keyword
     * @param mixed $value
     * @throws PivotException
     */
    public function setInfo($keyword, $value)
    {
        if (! is_string($keyword)) {
            throw new PivotException('A keyword string must be provided');
        }
        if (array_key_exists($keyword, $this->info)) {
            $this->info[$keyword] = (string) $value;
        }
    }

    public function clearSelectors()
    {
        $this->clearSelectorRows();
        $this->clearSelectorColumns();
        $this->clearFilters();
        $this->clearAggregates();
    }

    public function clearSelectorRows()
    {
        $this->rows = [];
    }

    public function clearFilters()
    {
        $this->filters->clear();
    }

    public function clearAggregates()
    {
        $this->aggregates->clear();
    }

    public function clearSelectorColumns()
    {
        $this->columns = [];
    }

    public function clearInfo()
    {
        $this->info = [
            'author' => '',
            'created' => date('Y-m-d H:i:s', time()),
            'description' => '',
            'protected' => 'no',
        ];
    }

    public function reset()
    {
        $this->clearInfo();
        $this->fields->clear();
        $this->clearSelectors();
    }

    public function addSourceField($fieldname, $caption, $type)
    {
        $this->fields->addItem(new Field($fieldname, $caption, $type));
    }

    public function addFilter($fieldname, $operator, $arguments)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Field $fieldname does not exists");
        }
        $this->filters->addItem(new Filter($fieldname, $operator, $arguments));
    }

    public function addColumn($fieldname)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Invalid field name $fieldname");
        }
        if (! in_array($fieldname, $this->columns)) {
            $this->columns[] = $fieldname;
        }
    }

    public function addRow($fieldname)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Invalid field name $fieldname");
        }
        if (! in_array($fieldname, $this->rows)) {
            $this->rows[] = $fieldname;
        }
    }

    /**
     * @param string $fieldname
     * @param string $asname
     * @param string $caption
     * @param string $agregatorfunction
     * @param int $decimals
     * @param string $order
     */
    public function addAggregate(
        $fieldname,
        $asname,
        $caption,
        $agregatorfunction,
        $decimals = 2,
        $order = Aggregate::ORDERNONE
    ) {
        static $counter = 0;
        if ('' === $asname) {
            $asname = '__agregator_' . (++$counter);
        }
        $this->aggregates->addItem(new Aggregate($fieldname, $asname, $caption, $agregatorfunction, $decimals, $order));
    }

    public function getSourceFields()
    {
        return $this->fields->asArray();
    }

    public function getCurrentFilters()
    {
        $return = $this->filters->asArray();
        foreach ($return as $i => $value) {
            $return[$i]['caption'] = $this->getFieldObject($value['fieldname'])->getCaption();
        }
        return $return;
    }

    public function getCurrentColumns()
    {
        $return = [];
        foreach ($this->columns as $column) {
            $field = $this->getFieldObject($column);
            $return[] = [
                'caption' => $field->getCaption(),
                'fieldname' => $field->getFieldname(),
            ];
        }
        return $return;
    }

    public function getCurrentRows()
    {
        $return = [];
        foreach ($this->rows as $row) {
            $field = $this->getFieldObject($row);
            $return[] = [
                'caption' => $field->getCaption(),
                'fieldname' => $field->getFieldname(),
            ];
        }
        return $return;
    }

    public function getCurrentAggregates()
    {
        return $this->aggregates->asArray();
    }

    /**
     * Perform a query to obtain the distinct values of a fieldname
     *
     * @param string $fieldname
     * @return array
     * @throws PivotException if the fieldname does not exists in the source fields
     */
    public function getPossibleValues($fieldname)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Field $fieldname does not exists in the source fields");
        }
        $sql = 'SELECT DISTINCT ' . $this->db->sqlFieldEscape($fieldname)
            . ' FROM ' . $this->sqlFrom()
            . ' ORDER BY ' . $this->db->sqlFieldEscape($fieldname)
            . ';';
        return $this->db->queryArrayOne($sql) ? : [];
    }

    /**
     * @return array
     * @throws PivotException
     * @throws PivotExceptionQuery
     */
    private function queryData()
    {
        // SQL: Selects
        $sqlSelectFields = $this->rows;
        $sqlSelectAggregates = [];
        foreach ($this->aggregates as $aggregate) {
            $sqlSelectAggregates[] = $aggregate->getSQL($this->db);
        }
        $sqlSelects = array_merge($sqlSelectFields, $sqlSelectAggregates);
        if (! count($sqlSelects)) { // nothing to select
            throw new PivotException('Nothing to query');
        }

        // SQL: Where
        $sqlWheres = [];
        foreach ($this->filters as $filter) {
            $sqlWheres[] = '('
                . $filter->getSQL($this->db, $this->getFieldObject($filter->getFieldname())->toDBAL())
                . ')';
        }

        // SQL Creation
        $sql = 'SELECT ' . implode(', ', $sqlSelects)
            . ' FROM ' . $this->sqlFrom()
            . ((count($sqlWheres)) ? ' WHERE ' . implode(' AND ', $sqlWheres) : '')
            . ((count($sqlSelectFields)) ? ' GROUP BY ' . implode(', ', $sqlSelectFields) . ' WITH ROLLUP' : '')
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
     * @return QueryResult
     */
    public function query()
    {
        return new QueryResult(
            $this->queryTotalsResult(true),
            $this->queryDetailsResult(),
            $this->getCurrentRows(),
            $this->getCurrentColumns(),
            $this->aggregates->asArray()
        );
    }

    /**
     * @param bool $sortValues
     * @return Result
     * @throws PivotException
     */
    private function queryTotalsResult($sortValues)
    {
        // variable to return
        $result = new Result('', '');

        // now all the data including rollup cells are in $data
        // we have to generate the output
        $data = $this->queryData();
        $dataCount = count($data);
        for ($i = 0; $i < $dataCount; $i++) {
            // create the values content
            $values = [];
            foreach ($this->aggregates as $aggregate) {
                $aggregateName = $aggregate->getAsname();
                $values[$aggregateName] = $data[$i][$aggregateName];
            }
            // ahora especificar donde va el resultado
            $ch = $result;
            foreach ($this->rows as $row) {
                $rowvalue = $data[$i][$row];
                if (is_null($rowvalue)) {
                    break;
                } else {
                    if (! $ch->children->exists($rowvalue)) {
                        // insert without value ($row is the $fieldname and $rowvalue is the $caption)
                        $ch->children->addItem(new Result($row, $rowvalue), $rowvalue);
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
            $ordering = new ResultOrdering($this->aggregates->getOrderArray());
            if ($ordering->isRequired()) {
                $result->orderBy($ordering);
            }
        }
        // format aggregate values after sort
        $this->formatResultValues($result);
        return $result;
    }

    private function formatResultValues(Result $result)
    {
        foreach ($this->aggregates as $aggregatorName => $aggregate) {
            if (null !== $value = $result->getCurrentValue($aggregatorName)) {
                $result->setCurrentValue($aggregatorName, number_format($value, $aggregate->getDecimals()));
            }
        }
        foreach ($result->children as $child) {
            $this->formatResultValues($child);
        }
    }

    /**
     * @return Result|null Null if no columns are set
     */
    private function queryDetailsResult()
    {
        if (! count($this->columns)) {
            return null;
        }
        $pivotDetails = clone $this;
        $pivotDetails->clearSelectorRows();
        $pivotDetails->clearSelectorColumns();
        foreach ($this->columns as $fieldname) {
            $pivotDetails->addRow($fieldname);
        }
        foreach ($this->rows as $fieldname) {
            $pivotDetails->addRow($fieldname);
        }
        $result = $pivotDetails->queryTotalsResult(false);
        $result->setAsNotRow(count($this->columns));
        return $result;
    }

    /**
     * Return an field object from the field list
     * @param string $fieldname
     * @return Field
     * @throws PivotException when field name does not exists
     */
    private function getFieldObject($fieldname)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Expected to find field $fieldname but it does not exists in the list");
        }
        return $this->fields->value($fieldname);
    }

    /**
     * Return the FROM part of the SQL statement
     * @return string
     */
    private function sqlFrom()
    {
        // do trim including ';' at the right part
        $sqlSource = rtrim(ltrim($this->source), "; \t\n\r\x0B\0");
        // check if begins with select, if so then insert into a subquery
        $pos = strpos(strtoupper($this->source), 'SELECT ');
        if ($pos === 0) {
            $sqlSource = "($sqlSource) AS __pivot__";
        } else {
            $sqlSource = $this->db->sqlTableEscape($sqlSource);
        }
        return $sqlSource;
    }
}
