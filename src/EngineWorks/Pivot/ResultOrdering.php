<?php
namespace EngineWorks\Pivot;

/**
 * Class to sort elements (ordering) of a result
 */
class ResultOrdering
{
    /** @var array */
    private $orderBy;

    /** @var bool */
    private $required;

    /**
     * ResultOrdering constructor
     * The parameter orderBy must contain the list of aggregates and the order they must use
     * [$aggregate1 => $order1, $aggregate2 => $order2, ... ]
     *
     * @param array $orderBy
     */
    public function __construct(array $orderBy)
    {
        $this->orderBy = $orderBy;
        $this->required = false;
        foreach ($orderBy as $criteria) {
            if ($criteria) {
                $this->required = true;
                break;
            }
        }
    }

    /**
     * Method to make comparisons
     *
     * @param Result $a
     * @param Result $b
     * @return int
     */
    public function orderItems(Result $a, Result $b)
    {
        // compare using every aggregator with its own order type
        foreach ($this->orderBy as $field => $order) {
            if ($order === Aggregate::ORDERASC) {
                return $this->comparePivotValues($field, $a, $b);
            } elseif ($order === Aggregate::ORDERDESC) {
                return $this->comparePivotValues($field, $b, $a);
            }
        }
        // if $a and $b are the same, order alphabetically by caption
        return strcasecmp($a->caption, $b->caption);
    }

    /**
     * Return if ordering is required based on the orderBy array
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Internal method to check two numerical values and return 0, -1 or 1
     *
     * @param string $field
     * @param Result $a
     * @param Result $b
     * @return int
     */
    protected function comparePivotValues($field, Result $a, Result $b)
    {
        return $a->values[$field] <=> $b->values[$field];
    }
}
