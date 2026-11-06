<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<int, Filter> */
class Filters implements IteratorAggregate, Countable
{
    /** @var array<int, Filter> */
    private array $items = [];

    public function addItem(Filter $item): void
    {
        $this->items[] = $item;
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /** @return list<array{fieldname: string, operator: string, arguments: mixed}> */
    public function asArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[] = $item->asArray();
        }
        return $array;
    }

    /** @return Traversable<int, Filter> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
