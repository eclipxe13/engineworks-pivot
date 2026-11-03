<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

/**
 * @extends Collection<Aggregate>
 */
class Aggregates extends Collection
{
    protected string $itemsInstanceOf = Aggregate::class;

    /**
     * @param Aggregate $item
     */
    public function addItem(mixed $item, int|string|null $key = null): static
    {
        $key = $item->getAsName();
        return parent::addItem($item, $key);
    }

    /**
     * @return list<array{fieldname: string, caption: string, group: string, asname: string, decimals: int, order: string}>
     */
    public function asArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[] = $item->asArray();
        }
        return $array;
    }

    /**
     * @return array<string, string>
     */
    public function getOrderArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[$item->getAsName()] = $item->getOrder();
        }
        return $array;
    }
}
