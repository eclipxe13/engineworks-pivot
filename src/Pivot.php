<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use Stringable;

class Pivot
{
    /**
     * Table name, View name or SELECT instruction to extract the values
     */
    private string $source;

    /**
     * Collection of all fields used in the source
     */
    private Fields $fields;

    /**
     * Collection of rules
     */
    private Filters $filters;

    /**
     * Collection of columns
     *
     * @var list<string>
     */
    private array $columns = [];

    /**
     * Collection of rows
     *
     * @var list<string>
     */
    private array $rows = [];

    /**
     * Collection of aggregates
     */
    private Aggregates $aggregates;

    /**
     * Information of extended information
     *
     * @var array<string, string>
     */
    private array $info = [];

    private static int $aggregateCounter = 0;

    public function __construct(string $source = '')
    {
        $this->fields = new Fields();
        $this->aggregates = new Aggregates();
        $this->filters = new Filters();
        $this->clearInfo();
        $this->setSource($source);
    }

    public function __clone(): void
    {
        // Force a copy of this->object, otherwise it will point to the same object.
        $this->fields = clone $this->fields;
        $this->filters = clone $this->filters;
        $this->aggregates = clone $this->aggregates;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Return a current value of information, or FALSE if the keyword does not exist.
     */
    public function getInfo(string $keyword): string|false
    {
        if (array_key_exists($keyword, $this->info)) {
            return $this->info[$keyword];
        }
        return false;
    }

    /**
     * Return the complete array of information values.
     *
     * @return array<string, string>
     */
    public function getInfoArray(): array
    {
        return $this->info;
    }

    /** @param scalar|Stringable|null $value */
    public function setInfo(string $keyword, mixed $value): void
    {
        if (array_key_exists($keyword, $this->info)) {
            $this->info[$keyword] = (string) $value;
        }
    }

    public function clearSelectors(): void
    {
        $this->clearSelectorRows();
        $this->clearSelectorColumns();
        $this->clearFilters();
        $this->clearAggregates();
    }

    public function clearSelectorRows(): void
    {
        $this->rows = [];
    }

    public function clearFilters(): void
    {
        $this->filters->clear();
    }

    public function clearAggregates(): void
    {
        $this->aggregates->clear();
    }

    public function clearSelectorColumns(): void
    {
        $this->columns = [];
    }

    public function clearInfo(): void
    {
        $this->info = [
            'author' => '',
            'created' => date('Y-m-d H:i:s', time()),
            'description' => '',
            'protected' => 'no',
        ];
    }

    public function reset(): void
    {
        $this->clearInfo();
        $this->fields->clear();
        $this->clearSelectors();
    }

    public function addSourceField(string $fieldName, string $caption, string $type): void
    {
        $this->fields->addItem(new Field($fieldName, $caption, $type));
    }

    public function addFilter(string $fieldName, string $operator, mixed $arguments): void
    {
        if (! $this->fields->exists($fieldName)) {
            throw new PivotException("Field $fieldName does not exist.");
        }
        $this->filters->addItem(new Filter($fieldName, $operator, $arguments));
    }

    public function addColumn(string $fieldName): void
    {
        if (! $this->fields->exists($fieldName)) {
            throw new PivotException("Invalid field name $fieldName.");
        }
        if (! in_array($fieldName, $this->columns, true)) {
            $this->columns[] = $fieldName;
        }
    }

    public function addRow(string $fieldName): void
    {
        if (! $this->fields->exists($fieldName)) {
            throw new PivotException("Invalid field name $fieldName.");
        }
        if (! in_array($fieldName, $this->rows, true)) {
            $this->rows[] = $fieldName;
        }
    }

    public function addAggregate(
        string $fieldName,
        string $asName,
        string $caption,
        string $aggregatorFunction,
        int $decimals = 2,
        string $order = Aggregate::ORDERNONE,
    ): void {
        if ('' === $asName) {
            self::$aggregateCounter++;
            $asName = '__aggregator_' . self::$aggregateCounter;
        }
        $this->aggregates->addItem(
            new Aggregate($fieldName, $asName, $caption, $aggregatorFunction, $decimals, $order)
        );
    }

    /** @return list<array{fieldname: string, caption: string, type: string}> */
    public function getSourceFields(): array
    {
        return $this->fields->asArray();
    }

    /** @return list<array{fieldname: string, operator: string, arguments: mixed, caption: string}> */
    public function getCurrentFilters(): array
    {
        $return = [];
        foreach ($this->filters->asArray() as $value) {
            $return[] = [
                'fieldname' => $value['fieldname'],
                'operator' => $value['operator'],
                'arguments' => $value['arguments'],
                'caption' => $this->getFieldElement($value['fieldname'])->getCaption(),
            ];
        }
        return $return;
    }

    /** @return list<array{caption: string, fieldname: string}> */
    public function getCurrentColumns(): array
    {
        $return = [];
        foreach ($this->columns as $column) {
            $field = $this->getFieldElement($column);
            $return[] = [
                'caption' => $field->getCaption(),
                'fieldname' => $field->getFieldName(),
            ];
        }
        return $return;
    }

    /** @return list<array{caption: string, fieldname: string}> */
    public function getCurrentRows(): array
    {
        $return = [];
        foreach ($this->rows as $row) {
            $field = $this->getFieldElement($row);
            $return[] = [
                'caption' => $field->getCaption(),
                'fieldname' => $field->getFieldName(),
            ];
        }
        return $return;
    }

    public function hasColumns(): bool
    {
        return count($this->columns) > 0;
    }

    public function hasRows(): bool
    {
        return count($this->rows) > 0;
    }

    /** @return list<array{fieldname: string, caption: string, group: string, asname: string, decimals: int, order: string}> */
    public function getCurrentAggregates(): array
    {
        return $this->aggregates->asArray();
    }

    /**
     * Return a field object from the field list
     *
     * @throws PivotException when field name does not exist
     */
    public function getFieldElement(string $fieldName): Field
    {
        if (! $this->fields->exists($fieldName)) {
            throw new PivotException("Expected to find field $fieldName but it does not exist in the list.");
        }
        $field = $this->fields->value($fieldName);
        if (null === $field) {
            throw new PivotException("Expected to find field $fieldName but it does not exist in the list.");
        }
        return $field;
    }

    public function getFieldsCollection(): Fields
    {
        return $this->fields;
    }

    public function getAggregatesCollection(): Aggregates
    {
        return $this->aggregates;
    }

    public function getFiltersCollection(): Filters
    {
        return $this->filters;
    }
}
