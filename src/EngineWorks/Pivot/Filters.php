<?php
namespace EngineWorks\Pivot;

class Filters extends Collection
{
    protected $itemsInstanceOf = Filter::class;

    public function addItem($item, $key = null)
    {
        return parent::addItem($item, null);
    }

    public function asArray()
    {
        $array = [];
        /* @var Filter $item */
        foreach ($this->items as $key => $item) {
            $array[$key] = $item->asArray();
        }
        return $array;
    }
}
