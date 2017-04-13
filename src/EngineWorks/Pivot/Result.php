<?php
namespace EngineWorks\Pivot;

/**
 * Class to present Groups of results
 *
 * @property string $fieldname
 * @property string $caption
 * @property bool $isrow
 * @property array $values
 * @property Results|Result[] $children
 * @property Result $parent
 */
class Result
{
    /** @var string */
    private $privFieldname;

    /** @var string */
    private $privCaption;

    /** @var array|null */
    private $privValues;

    /** @var Results|Result[] */
    private $privChildren;

    /** @var Result */
    private $privParent;

    /** @var bool */
    private $privIsRow;

    public function __construct($fieldname, $caption, $values = null, $isrow = true)
    {
        $this->fieldname = $fieldname;
        $this->caption = $caption;
        $this->values = $values;
        $this->isrow = $isrow;
        $this->privChildren = null;
    }

    public function __clone()
    {
        throw new PivotException('Result cannot be cloned');
    }

    public function __set($name, $value)
    {
        if ('fieldname' == $name) {
            $this->privFieldname = (string) $value;
        } elseif ('caption' == $name) {
            $this->privCaption = (string) $value;
        } elseif ('values' == $name) {
            if (! is_null($value) && ! is_array($value)) {
                throw new PivotException('Property values must be null or array');
            } else {
                $this->privValues = $value;
            }
        } elseif ('isrow' == $name) {
            $this->privIsRow = (bool) $value;
        } elseif ('children' == $name) {
            throw new PivotException('Property children is read only');
        } elseif ('parent' == $name) {
            if (! is_null($this->privParent)) {
                throw new PivotException('Property parent has been set already');
            }
            if (! ($value instanceof self)) {
                throw new PivotException('Property parent must be a Result');
            }
            $this->privParent = $value;
        } else {
            throw new PivotException("Property $name does not exists");
        }
        return null;
    }

    public function __get($name)
    {
        if ('isrow' === $name) {
            $propname = 'privIsRow';
        } else {
            $propname = 'priv' . ucfirst($name);
        }
        if (! property_exists($this, $propname)) {
            throw new PivotException("Property $name does not exists");
        }
        // late creation of children property, created only when used
        if ($name == 'children' && is_null($this->privChildren)) {
            $this->privChildren = new Results($this);
        }
        return $this->{$propname};
    }

    /**
     * @param array $path
     * @return array|null
     */
    public function searchValue(array $path)
    {
        // if this is the element
        if (count($path) == 0) {
            return $this->privValues;
        }
        // get the first part to check in
        $search = array_shift($path);
        // if exists then search inside
        if ($this->children->exists($search)) { // if found
            return $this->children->value($search)->searchValue($path);
        }
        return null;
    }

    public function getPath($excludeRoot = true)
    {
        // I'm the root and is required to exclude
        if ($excludeRoot && is_null($this->privParent)) {
            return [];
        }
        // define me as an array
        $me = [$this->privCaption];
        if (! is_null($this->privParent)) {
            return array_merge($this->privParent->getPath($excludeRoot), $me);
        }
        // I'm not the root
        return $me;
    }

    public function hasChildren()
    {
        return (null !== $this->privChildren && $this->privChildren->Count() > 0);
    }

    public function setAsNotRow($childrenLevels = 0)
    {
        $this->privIsRow = false;
        if ($this->hasChildren() && $childrenLevels > 0) {
            foreach ($this->children as $child) {
                $child->setAsNotRow($childrenLevels - 1);
            }
        }
    }

    /**
     * @param bool|null $asrows
     * @return int
     */
    public function getDepth($asrows = null)
    {
        if (! $this->hasChildren()) {
            return 0;
        }
        $level = 0;
        foreach ($this->children as $child) {
            if (null === $asrows || $asrows == $child->isrow) {
                $level = max($level, $child->getDepth() + 1);
            }
        }
        return $level;
    }

    public function getCurrentDepth()
    {
        if (null === $this->parent) {
            return 0;
        }
        return $this->parent->getCurrentDepth() + 1;
    }

    /**
     * @param bool|null $asrows
     * @return int
     */
    public function getHorizontalDepth($asrows = null)
    {
        if (! $this->hasChildren()) {
            return 1;
        }
        $sum = 0;
        foreach ($this->children as $child) {
            if (null === $asrows || $asrows === $child->isrow) {
                $sum += $child->getHorizontalDepth();
            }
        }
        return $sum;
    }

    public function copy($childrenLevels)
    {
        $tree = new self($this->fieldname, $this->caption, $this->values, $this->isrow);
        if ($childrenLevels > 0 && $this->hasChildren()) {
            foreach ($this->children as $key => $child) {
                $tree->children->addItem($child->copy($childrenLevels - 1), $key);
            }
        }
        return $tree;
    }

    public function toText($includevalues = false)
    {
        $textvalues = [];
        if ($includevalues) {
            if (is_array($this->values)) {
                foreach ($this->values as $key => $value) {
                    $textvalues[] = $key . ' => ' . $value;
                }
            }
        }
        return implode(', ', [
            $this->privFieldname,
            $this->privCaption,
            'children: ' . (($this->hasChildren()) ? $this->children->count() : 'NO'),
            '[' . implode(', ', $textvalues) . ']',
        ]);
    }

    public function toTextTree($includevalues = false, $level = 0)
    {
        $tabs = str_repeat("\t", $level);
        $chtext = $tabs . $this->toText($includevalues);
        if ($this->hasChildren()) {
            foreach ($this->children as $child) {
                $chtext .= "\n" . $tabs . '' . $child->toTextTree($includevalues, $level + 1);
            }
        }
        return $chtext;
    }

    /**
     * @return Result[]
     */
    public function getLastChildrenArray()
    {
        // no children
        if (! $this->hasChildren()) {
            return [$this];
        }
        $array = [];
        foreach ($this->children as $child) {
            $array = array_merge($array, $child->getLastChildrenArray());
        }
        return $array;
    }

    public function getCurrentValue(string $key)
    {
        if (is_array($this->privValues) && array_key_exists($key, $this->privValues)) {
            return $this->privValues[$key];
        }
        return null;
    }

    public function setCurrentValue(string $key, $value)
    {
        if (is_array($this->privValues) && array_key_exists($key, $this->privValues)) {
            $this->privValues[$key] = $value;
        }
    }

    public function orderBy(ResultOrdering $ordering)
    {
        $this->children->orderBy($ordering);
        foreach ($this->children as $child) {
            $child->orderBy($ordering);
        }
    }
}
