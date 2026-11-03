<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

/**
 * Class to sort elements (ordering) of a result
 */
class ResultOrdering
{
    /** @var array<string, string> */
    private array $orderBy;

    private bool $required;

    /**
     * ResultOrdering constructor
     * The parameter orderBy must contain the list of aggregates and the order they must use
     * [$aggregate1 => $order1, $aggregate2 => $order2, ... ]
     *
     * @param array<string, string> $orderBy
     */
    public function __construct(array $orderBy)
    {
        $this->orderBy = $orderBy;
        $this->required = false;
        foreach ($orderBy as $criteria) {
            if ('' !== $criteria) {
                $this->required = true;
                break;
            }
        }
    }

    /**
     * Method to make comparisons
     */
    public function orderItems(mixed $a, mixed $b): int
    {
        if (! $a instanceof Result || ! $b instanceof Result) {
            return 0;
        }
        // compare using every aggregator with its own order type
        foreach ($this->orderBy as $field => $order) {
            if (Aggregate::ORDERASC === $order) {
                return $this->comparePivotValues($field, $a, $b);
            }
            if (Aggregate::ORDERDESC === $order) {
                return $this->comparePivotValues($field, $b, $a);
            }
        }
        // if $a and $b are the same, order alphabetically by caption
        return strcasecmp($a->caption, $b->caption);
    }

    /**
     * Return if ordering is required based on the orderBy array
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Internal method to check two values and return 0, -1 or 1
     */
    protected function comparePivotValues(string $field, Result $a, Result $b): int
    {
        return $a->getCurrentValue($field) <=> $b->getCurrentValue($field);
    }
}
