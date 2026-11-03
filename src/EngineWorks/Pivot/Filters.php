<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

/**
 * @extends Collection<Filter>
 */
class Filters extends Collection
{
    protected string $itemsInstanceOf = Filter::class;

    /**
     * @param Filter $item
     */
    public function addItem(mixed $item, int|string|null $key = null): static
    {
        return parent::addItem($item, null);
    }

    /**
     * @return list<array{fieldname: string, operator: string, arguments: mixed}>
     */
    public function asArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[] = $item->asArray();
        }
        return $array;
    }
}
