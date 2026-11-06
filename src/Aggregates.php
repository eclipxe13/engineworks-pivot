<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<string, Aggregate> */
class Aggregates implements IteratorAggregate, Countable
{
    /** @var array<string, Aggregate> */
    private array $items = [];

    public function addItem(Aggregate $item): void
    {
        $this->items[$item->getAsName()] = $item;
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /** @return list<array{fieldname: string, caption: string, group: string, asname: string, decimals: int, order: string}> */
    public function asArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[] = $item->asArray();
        }
        return $array;
    }

    /** @return array<string, string> */
    public function getOrderArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[$item->getAsName()] = $item->getOrder();
        }
        return $array;
    }

    /** @return Traversable<string, Aggregate> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
