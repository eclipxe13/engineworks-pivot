<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<int|string, Result> */
class Results implements IteratorAggregate, Countable
{
    /** @var array<int|string, Result> */
    private array $items = [];

    public function __construct(private readonly Result $parent)
    {
    }

    public function addItem(Result $item, int|string $key): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $item->setParent($this->parent);
        $this->items[$key] = $item;
    }

    public function orderBy(ResultOrdering $ordering): void
    {
        // uasort — Sort an array with a user-defined comparison function and maintain index association
        uasort($this->items, [$ordering, 'orderItems']);
    }

    public function exists(int|string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function value(int|string $key): Result|null
    {
        return $this->items[$key] ?? null;
    }

    /** @return Traversable<int|string, Result> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
