<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

class QueryResult
{
    /** @var Result */
    private $totals;

    /** @var Result */
    private $details;

    /** @var array */
    private $rows;

    /** @var array */
    private $columns;

    /** @var array */
    private $aggregates;

    /**
     * QueryResult constructor.
     * @param Result $totals
     * @param Result|null $details
     * @param array $rows
     * @param array $columns
     * @param array $aggregates
     */
    public function __construct(Result $totals, $details, array $rows, array $columns, array $aggregates)
    {
        $this->totals = $totals;
        $this->details = ($details instanceof Result) ? $details : null;
        $this->rows = $rows;
        $this->columns = $columns;
        $this->aggregates = $aggregates;
    }

    /**
     * @return Result
     */
    public function getTotals(): Result
    {
        return $this->totals;
    }

    /**
     * @return Result
     * @throws PivotException if there are not details
     */
    public function getDetails(): Result
    {
        if (null === $this->details) {
            throw new PivotException('The result does not have details');
        }
        return $this->details;
    }

    public function hasDetails() : bool
    {
        return (null !== $this->details);
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getAggregates(): array
    {
        return $this->aggregates;
    }
}
