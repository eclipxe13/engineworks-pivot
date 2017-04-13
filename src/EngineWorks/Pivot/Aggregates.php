<?php
namespace EngineWorks\Pivot;

class Aggregates extends Collection
{
    protected $itemsInstanceOf = Aggregate::class;

    public function addItem($item, $key = null)
    {
        $key = $item->getAsname();
        return parent::addItem($item, $key);
    }

    public function asArray()
    {
        $a = [];
        foreach ($this->items as $item) {
            /* @var Aggregate $item */
            $a[] = $item->asArray();
        }
        return $a;
    }

    /**
     * @return array
     */
    public function getOrderArray()
    {
        $array = [];
        foreach ($this->items as $key => $item) {
            /* @var Aggregate $item */
            $array[$key] = $item->getOrder();
        }
        return $array;
    }
}
