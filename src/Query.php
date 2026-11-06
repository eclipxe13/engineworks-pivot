<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use EngineWorks\DBAL\DBAL;
use Throwable;

class Query
{
    public function __construct(private readonly DBAL $db, private readonly Pivot $pivot)
    {
    }

    public function getDb(): DBAL
    {
        return $this->db;
    }

    public function getPivot(): Pivot
    {
        return $this->pivot;
    }

    /**
     * Perform a query to obtain the distinct values of a field name
     *
     * @return array<int, scalar|null>
     * @throws PivotException if the field name does not exist in the source fields
     */
    public function queryDistinct(string $fieldName): array
    {
        if (! $this->pivot->getFieldsCollection()->exists($fieldName)) {
            throw new PivotException("Field $fieldName does not exist in the source fields.");
        }
        $sql = 'SELECT DISTINCT ' . $this->db->sqlFieldEscape($fieldName)
            . ' FROM ' . $this->sqlFrom()
            . ' ORDER BY ' . $this->db->sqlFieldEscape($fieldName)
            . ';';
        return $this->db->queryArrayOne($sql) ?: [];
    }

    public function query(): QueryResult
    {
        return new QueryResult(
            $this->queryTotalsResult(true),
            $this->pivot->hasColumns() ? $this->queryDetailsResult() : null,
            $this->pivot->getCurrentRows(),
            $this->pivot->getCurrentColumns(),
            $this->pivot->getCurrentAggregates()
        );
    }

    /**
     * @return array<int, array<string, scalar|null>>
     * @throws PivotException
     * @throws PivotExceptionQuery
     */
    private function queryData(): array
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
        $sqlAllFields = [...$sqlSelects, ...$sqlAggregates];
        if (0 === count($sqlAllFields)) { // nothing to select
            throw new PivotException('Nothing to query.');
        }

        // get all elements from filter
        $sqlWheres = [];
        foreach ($this->pivot->getFiltersCollection() as $filter) {
            $sqlWheres[] = '('
                . $filter->getSQL($this->db, $this->pivot->getFieldElement($filter->getFieldName())->toDBAL())
                . ')';
        }
        // SQL Creation
        $sql = 'SELECT ' . implode(', ', $sqlAllFields)
            . ' FROM ' . $this->sqlFrom()
            . ([] !== $sqlWheres ? ' WHERE ' . implode(' AND ', $sqlWheres) : '')
            . ([] !== $sqlSelects ? ' GROUP BY ' . implode(', ', $sqlSelects) . ' WITH ROLLUP' : '')
            . ';';

        // retrieve and return the data
        try {
            $data = $this->db->queryArrayValues($sql);
        } catch (Throwable $exception) {
            throw new PivotExceptionQuery('Exception when query', $sql, previous: $exception);
        }
        if (! is_array($data)) {
            throw new PivotExceptionQuery('Error when query: ' . $this->db->getLastMessage(), $sql);
        }
        return $data;
    }

    /** @throws PivotException */
    private function queryTotalsResult(bool $sortValues): Result
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
                $aggregateName = $aggregate->getAsName();
                $values[$aggregateName] = $data[$i][$aggregateName];
            }
            // now specify where the result goes
            $ch = $result;
            foreach ($this->pivot->getCurrentRows() as $row) {
                $rowValue = $data[$i][$row['fieldname']];
                if (null === $rowValue) {
                    break;
                }
                $rowKey = (string) $rowValue;
                $children = $ch->getChildren();
                if (! $children->exists($rowKey)) {
                    // insert without value ($row is the field name and $rowValue is the $caption)
                    $children->addItem(new Result($row['fieldname'], $rowKey), $rowKey);
                }
                $child = $children->value($rowKey);
                if (null === $child) {
                    throw new PivotException('Cannot find the child result when filling the results.');
                }
                $ch = $child;
            }
            // assign the values to the selected node
            if (null !== $ch->values) {
                throw new PivotException('Duplicated values when filling the results.');
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

    private function formatResultValues(Aggregates $aggregates, Result $result): void
    {
        foreach ($aggregates as $aggregate) {
            $aggregateName = $aggregate->getAsName();
            $value = $result->getCurrentValue($aggregateName);
            if (null !== $value) {
                $result->setCurrentValue($aggregateName, number_format((float) $value, $aggregate->getDecimals()));
            }
        }
        foreach ($result->getChildren() as $child) {
            $this->formatResultValues($aggregates, $child);
        }
    }

    /** @throws PivotException If no columns are defined */
    private function queryDetailsResult(): Result
    {
        $columns = $this->pivot->getCurrentColumns();
        if (0 === count($columns)) {
            throw new PivotException('Cannot get details if no columns are defined.');
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
     */
    private function sqlFrom(): string
    {
        // do trim including ';' at the right part
        $sqlSource = rtrim(ltrim($this->pivot->getSource()), "; \t\n\r\x0B\0");

        // check if begins with select, if so then insert into a subquery
        if (str_starts_with(strtoupper($sqlSource), 'SELECT ')) {
            return sprintf('(%s) AS __pivot__', $sqlSource);
        }

        return $this->db->sqlTableEscape($sqlSource);
    }
}
