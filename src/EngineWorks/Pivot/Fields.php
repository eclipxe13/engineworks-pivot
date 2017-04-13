<?php
namespace EngineWorks\Pivot;

class Fields extends Collection
{
    protected $itemsInstanceOf = Field::class;

    public function addItem($item, $key = null)
    {
        $key = $item->getFieldname();
        parent::addItem($item, $key);
    }

    public function asArray()
    {
        $array = [];
        /* @var Field $item */
        foreach ($this->items as $item) {
            $array[] = $item->asArray();
        }
        return $array;
    }
}
