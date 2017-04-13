<?php
namespace EngineWorks\Pivot;

class Results extends Collection
{
    protected $itemsInstanceOf = Result::class;

    /** @var Result */
    private $parent;

    public function __construct(Result $parent)
    {
        $this->parent = $parent;
    }

    public function addItem($item, $key = null)
    {
        /* @var Result $item */
        $item->parent = $this->parent;
        return parent::addItem($item, $key);
    }

    public function orderBy(ResultOrdering $ordering)
    {
        // uasort — Sort an array with a user-defined comparison function and maintain index association
        uasort($this->items, [$ordering, 'orderItems']);
    }
}
