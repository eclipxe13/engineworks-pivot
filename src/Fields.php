<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<string, Field> */
class Fields implements IteratorAggregate, Countable
{
    /** @var array<string, Field> */
    private array $items = [];

    public function addItem(Field $item): void
    {
        $this->items[$item->getFieldName()] = $item;
    }

    public function exists(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function value(string $key): Field|null
    {
        return $this->items[$key] ?? null;
    }

    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * @return list<array{fieldname: string, caption: string, type: string}>
     */
    public function asArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[] = $item->asArray();
        }
        return $array;
    }

    /** @return Traversable<string, Field> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
