<?php
namespace EngineWorks\Pivot;

/**
 * Simple collection class, you can use this to have a collections of members accesed by key or numeric
 *
 * The members of the collection are accesible:
 * - as properties: $col->member (read, write)
 * - as array: $col['member'] (read, write)
 * - per object invocation $col('member') (read)
 * - per method invocation $col->value('member') (read), $col->addItem($item, 'member') (write)
 *
 * Admits the isset and unset and count as alias of exists, remove and count methods
 * Implements the code to allow object cloning
 *
 * Contains an optional policy to ensure that items follow an specific class or interface
 *
 * The members can be inserted without key (just append the element to the end) or by a named key
 * if the key exists it is overwritten
 *
 * You also can extend this class to include your own rules
 *
 * As PHP does not support changing signatures, you cannot specify specific types on overrides
 * Try to use DocBlocks on your code to set the specific types
 *
 * @package EngineWorks\Utils
 *
 */
class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * This is the internal items container
     * @var array
     */
    protected $items = [];

    /**
     * Set this property to the class name to set the item class policy
     * @var string
     */
    protected $itemsInstanceOf = '';

    /**
     * When this is TRUE an exception is thrown when access a member that does not exists
     * When is FALSE then it only returns NULL
     * @var bool
     */
    protected $throwExceptionItemNotFound = true;

    /*
     *
     * Real methods
     *
     */

    /**
     * Remove all elements from the collection
     */
    public function clear()
    {
        $this->items = [];
    }

    /**
     * Count of elements
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Return true if the element exists (even when the content is null)
     * @param string|int $key
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Throw a LogicException if an item does not follow the ItemsClass policy
     * @param mixed $item
     */
    protected function checkItemInstanceOf($item)
    {
        if (! $this->isItemInstanceOf($item)) {
            throw new \LogicException(get_class($this) . " error, item is not an instance of {$this->itemsInstanceOf}");
        }
    }

    /**
     * @param mixed $item
     * @param string|int $key
     * @return $this
     */
    public function addItem($item, $key = null)
    {
        $this->checkItemInstanceOf($item);
        if (null === $key) {
            $this->items[] = $item;
        } else {
            $this->items[$key] = $item;
        }
        return $this;
    }

    public function addItems($items)
    {
        foreach ($items as $key => $item) {
            $this->addItem($item, $key);
        }
    }

    /**
     * @param string|int $key
     * @return $this
     */
    public function remove($key)
    {
        if (array_key_exists($key, $this->items)) {
            unset($this->items[$key]);
        }
        return $this;
    }

    /**
     * An array only with the keys
     * @return array
     */
    public function keys()
    {
        return array_keys($this->items);
    }

    /**
     * An array only with the values (no keys)
     * @return array
     */
    public function values()
    {
        return array_values($this->items);
    }

    /**
     * An element from the collection if found, if not found it will:
     * If throwExceptionItemNotFound is true then it will create an exception
     * Otherwise will return null
     *
     * @param mixed $key
     * @return mixed
     * @throws \OutOfRangeException If protected property $throwExceptionItemNotFound is true
     */
    public function value($key)
    {
        if (! array_key_exists($key, $this->items)) {
            if ($this->throwExceptionItemNotFound) {
                throw new \OutOfRangeException("Key '$key' does not exists");
            }
            return null;
        }
        return $this->items[$key];
    }

    /**
     * The full array of key-values
     * @return array
     */
    public function keyValues()
    {
        return $this->items;
    }

    /**
     * Implementation of IteratorAggregate
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Check if an item compliance with the ItemsClass policy
     * @param mixed $item
     * @return bool
     */
    protected function isItemInstanceOf($item)
    {
        if ('' === $this->itemsInstanceOf) {
            return true;
        }
        if (! is_object($item)) {
            return false;
        }
        if (! $item instanceof $this->itemsInstanceOf) {
            return false;
        }
        return true;
    }

    /**
     * Set the items class policy, if elements already exists each one is checked
     * @param string $classname
     */
    public function setItemsClassPolicy($classname)
    {
        if (! is_string($classname)) {
            throw new \InvalidArgumentException(get_class($this) . ' error, setItemsClass expects a string value');
        }
        $this->itemsInstanceOf = $classname;
        if ('' === $this->itemsInstanceOf) {
            return;  // avoid checks
        }
        foreach ($this->items as $key => $item) {
            if (! $this->isItemInstanceOf($item)) {
                throw new \LogicException(
                    get_class($this) . " error, the item $key is not an instance of {$this->itemsInstanceOf}"
                );
            }
        }
    }

    /**
     * Get the items class policy, if empty string then no policy is set
     * @return string
     */
    public function getItemsClassPolicy()
    {
        return $this->itemsInstanceOf;
    }

    /*
     *
     * Magic methods
     *
     */

    public function __isset($key)
    {
        return $this->exists($key);
    }

    public function __get($key)
    {
        return $this->value($key);
    }

    public function __set($key, $value)
    {
        $this->addItem($value, $key);
    }

    public function __unset($key)
    {
        $this->remove($key);
    }

    public function __invoke($key)
    {
        return $this->value($key);
    }

    public function __clone()
    {
        foreach ($this->items as $key => $item) {
            if ($item != null and is_object($item)) {
                $this->items[$key] = clone $item;
            }
        }
    }

    /*
     *
     * ArrayAccess methods
     *
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->value($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->addItem($value, $offset);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
