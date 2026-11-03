<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use OutOfRangeException;
use Traversable;

/**
 * Simple collection class to store members accessible by key or numeric index.
 *
 * The members are inserted without key (just append) or by a named key.
 * If the key already exists, the element is overwritten.
 *
 * Contains an optional policy to ensure that items follow a specific class or interface.
 *
 * @template TItem
 * @implements IteratorAggregate<int|string, TItem>
 */
class Collection implements IteratorAggregate, Countable
{
    /**
     * This is the internal items container
     *
     * @var array<int|string, TItem>
     */
    protected array $items = [];

    /**
     * Set this property to the class name to set the item class policy
     */
    protected string $itemsInstanceOf = '';

    /**
     * When this is TRUE an exception is thrown when accessing a member that does not exists
     * When is FALSE then it only returns NULL
     */
    protected bool $throwExceptionItemNotFound = true;

    /**
     * Remove all elements from the collection
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Count of elements
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Return true if the element exists (even when the content is null)
     */
    public function exists(int|string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Throw a LogicException if an item does not follow the ItemsClass policy
     *
     * @param TItem $item
     */
    protected function checkItemInstanceOf(mixed $item): void
    {
        if (! $this->isItemInstanceOf($item)) {
            throw new LogicException(static::class . " error, item is not an instance of {$this->itemsInstanceOf}.");
        }
    }

    /**
     * @param TItem $item
     * @return $this
     */
    public function addItem(mixed $item, int|string|null $key = null): static
    {
        $this->checkItemInstanceOf($item);
        if (null === $key) {
            $this->items[] = $item;
        } else {
            $this->items[$key] = $item;
        }
        return $this;
    }

    /**
     * @param iterable<int|string, TItem> $items
     */
    public function addItems(iterable $items): void
    {
        foreach ($items as $key => $item) {
            $this->addItem($item, $key);
        }
    }

    public function remove(int|string $key): static
    {
        if (array_key_exists($key, $this->items)) {
            unset($this->items[$key]);
        }
        return $this;
    }

    /**
     * An array only with the keys
     *
     * @return list<int|string>
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * An array only with the values (no keys)
     *
     * @return list<TItem>
     */
    public function values(): array
    {
        return array_values($this->items);
    }

    /**
     * An element from the collection if found, if not found it will:
     * If throwExceptionItemNotFound is true then it will create an exception
     * Otherwise will return null
     *
     * @return TItem|null
     * @throws OutOfRangeException If protected property $throwExceptionItemNotFound is true
     */
    public function value(int|string $key)
    {
        if (! array_key_exists($key, $this->items)) {
            if ($this->throwExceptionItemNotFound) {
                throw new OutOfRangeException("Key '$key' does not exist.");
            }
            return null;
        }
        return $this->items[$key];
    }

    /**
     * The full array of key-values
     *
     * @return array<int|string, TItem>
     */
    public function keyValues(): array
    {
        return $this->items;
    }

    /**
     * Implementation of IteratorAggregate
     *
     * @return Traversable<int|string, TItem>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Check if an item compliance with the ItemsClass policy
     */
    protected function isItemInstanceOf(mixed $item): bool
    {
        if ('' === $this->itemsInstanceOf) {
            return true;
        }
        if (! is_object($item)) {
            return false;
        }
        return $item instanceof $this->itemsInstanceOf;
    }

    /**
     * Set the items class policy, if elements already exists each one is checked
     */
    public function setItemsClassPolicy(string $className): void
    {
        $this->itemsInstanceOf = $className;
        if ('' === $this->itemsInstanceOf) {
            return;  // avoid checks
        }
        foreach ($this->items as $key => $item) {
            if (! $this->isItemInstanceOf($item)) {
                throw new LogicException(
                    static::class . " error, the item $key is not an instance of {$this->itemsInstanceOf}."
                );
            }
        }
    }

    /**
     * Get the items class policy, if empty string then no policy is set
     */
    public function getItemsClassPolicy(): string
    {
        return $this->itemsInstanceOf;
    }

    public function __clone(): void
    {
        foreach ($this->items as $key => $item) {
            if (null !== $item && is_object($item)) {
                $this->items[$key] = clone $item;
            }
        }
    }
}
