<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

/**
 * Class to present Groups of results
 */
class Result
{
    private Results|null $children = null;

    private Result|null $parent = null;

    /**
     * @param array<string, scalar|null>|null $values
     */
    public function __construct(public readonly string $fieldName, public string $caption, public array|null $values = null, public bool $isRow = true)
    {
    }

    public function __clone(): void
    {
        throw new PivotException('Result cannot be cloned.');
    }

    public function getChildren(): Results
    {
        // late creation of children, created only when used
        $this->children ??= new Results($this);
        return $this->children;
    }

    public function getParent(): self|null
    {
        return $this->parent;
    }

    public function setParent(self $parent): void
    {
        if (null !== $this->parent) {
            throw new PivotException('Property parent has been set already.');
        }
        $this->parent = $parent;
    }

    /**
     * @param list<string> $path
     * @return array<string, scalar|null>|null
     */
    public function searchValue(array $path): array|null
    {
        // if this is the element
        if ([] === $path) {
            return $this->values;
        }
        // get the first part to check in
        $search = array_shift($path);
        // if the element does not exist then nothing to search
        if (! $this->getChildren()->exists($search)) {
            return null;
        }
        $child = $this->getChildren()->value($search);
        return $child?->searchValue($path);
    }

    /**
     * @return list<string>
     */
    public function getPath(bool $excludeRoot = true): array
    {
        // I'm the root and is required to exclude
        if ($excludeRoot && null === $this->parent) {
            return [];
        }
        // define me as an array
        $me = [$this->caption];
        $parent = $this->parent;
        if (null !== $parent) {
            return array_merge($parent->getPath($excludeRoot), $me);
        }
        // I'm the root
        return $me;
    }

    public function hasChildren(): bool
    {
        return null !== $this->children && $this->children->count() > 0;
    }

    public function setAsNotRow(int $childrenLevels = 0): void
    {
        $this->isRow = false;
        if ($this->hasChildren() && $childrenLevels > 0) {
            foreach ($this->getChildren() as $child) {
                $child->setAsNotRow($childrenLevels - 1);
            }
        }
    }

    public function getDepth(bool|null $asRows = null): int
    {
        if (! $this->hasChildren()) {
            return 0;
        }
        $level = 0;
        foreach ($this->getChildren() as $child) {
            if (null === $asRows || $asRows === $child->isRow) {
                $level = max($level, $child->getDepth() + 1);
            }
        }
        return $level;
    }

    public function getCurrentDepth(): int
    {
        $parent = $this->parent;
        if (null === $parent) {
            return 0;
        }
        return $parent->getCurrentDepth() + 1;
    }

    public function getHorizontalDepth(bool|null $asRows = null): int
    {
        if (! $this->hasChildren()) {
            return 1;
        }
        $sum = 0;
        foreach ($this->getChildren() as $child) {
            if (null === $asRows || $asRows === $child->isRow) {
                $sum += $child->getHorizontalDepth();
            }
        }
        return $sum;
    }

    public function copy(int $childrenLevels): self
    {
        $tree = new self($this->fieldName, $this->caption, $this->values, $this->isRow);
        if ($childrenLevels > 0 && $this->hasChildren()) {
            foreach ($this->getChildren()->getIterator() as $key => $child) {
                $tree->getChildren()->addItem($child->copy($childrenLevels - 1), $key);
            }
        }
        return $tree;
    }

    public function toText(bool $includeValues = false): string
    {
        $textValues = [];
        if ($includeValues && is_array($this->values)) {
            foreach ($this->values as $key => $value) {
                $textValues[] = sprintf('%s => %s', $key, $value);
            }
        }
        return implode(', ', [
            $this->fieldName,
            $this->caption,
            'children: ' . (($this->hasChildren()) ? $this->getChildren()->count() : 'NO'),
            '[' . implode(', ', $textValues) . ']',
        ]);
    }

    public function toTextTree(bool $includeValues = false, int $level = 0): string
    {
        $tabs = str_repeat("\t", $level);
        $chtext = $tabs . $this->toText($includeValues);
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $child) {
                $chtext .= "\n" . $tabs . $child->toTextTree($includeValues, $level + 1);
            }
        }
        return $chtext;
    }

    /**
     * @return list<Result>
     */
    public function getLastChildrenArray(): array
    {
        // no children
        if (! $this->hasChildren()) {
            return [$this];
        }
        $array = [];
        foreach ($this->getChildren() as $child) {
            $array = array_merge($array, $child->getLastChildrenArray());
        }
        return $array;
    }

    /**
     * @return scalar|null
     */
    public function getCurrentValue(string $key)
    {
        if (is_array($this->values) && array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }
        return null;
    }

    /**
     * @param scalar|null $value
     */
    public function setCurrentValue(string $key, mixed $value): void
    {
        if (is_array($this->values) && array_key_exists($key, $this->values)) {
            $this->values[$key] = $value;
        }
    }

    public function orderBy(ResultOrdering $ordering): void
    {
        $this->getChildren()->orderBy($ordering);
        foreach ($this->getChildren() as $child) {
            $child->orderBy($ordering);
        }
    }
}
