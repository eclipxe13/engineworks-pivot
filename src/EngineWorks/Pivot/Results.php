<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

/**
 * @extends Collection<Result>
 */
class Results extends Collection
{
    protected string $itemsInstanceOf = Result::class;

    private Result $parent;

    public function __construct(Result $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param Result $item
     */
    public function addItem(mixed $item, int|string|null $key = null): static
    {
        $item->setParent($this->parent);
        return parent::addItem($item, $key);
    }

    public function orderBy(ResultOrdering $ordering): void
    {
        // uasort — Sort an array with a user-defined comparison function and maintain index association
        uasort($this->items, [$ordering, 'orderItems']);
    }
}
