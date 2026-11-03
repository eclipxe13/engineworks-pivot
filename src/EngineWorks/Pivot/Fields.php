<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

/**
 * @extends Collection<Field>
 */
class Fields extends Collection
{
    protected string $itemsInstanceOf = Field::class;

    /**
     * @param Field $item
     */
    public function addItem(mixed $item, int|string|null $key = null): static
    {
        $key = $item->getFieldName();
        parent::addItem($item, $key);
        return $this;
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
}
