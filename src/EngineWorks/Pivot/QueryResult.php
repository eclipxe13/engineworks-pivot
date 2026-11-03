<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

class QueryResult
{
    /**
     * @param list<array{caption: string, fieldname: string}> $rows
     * @param list<array{caption: string, fieldname: string}> $columns
     * @param list<array{fieldname: string, caption: string, group: string, asname: string, decimals: int, order: string}> $aggregates
     */
    public function __construct(private readonly Result $totals, private readonly Result|null $details, private readonly array $rows, private readonly array $columns, private readonly array $aggregates)
    {
    }

    public function getTotals(): Result
    {
        return $this->totals;
    }

    /**
     * @throws PivotException if there are not details
     */
    public function getDetails(): Result
    {
        if (null === $this->details) {
            throw new PivotException('The result does not have details.');
        }
        return $this->details;
    }

    public function hasDetails(): bool
    {
        return null !== $this->details;
    }

    /**
     * @return list<array{caption: string, fieldname: string}>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return list<array{caption: string, fieldname: string}>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return list<array{fieldname: string, caption: string, group: string, asname: string, decimals: int, order: string}>
     */
    public function getAggregates(): array
    {
        return $this->aggregates;
    }
}
