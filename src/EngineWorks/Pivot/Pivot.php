<?php
namespace EngineWorks\Pivot;

class Pivot
{
    /**
     * Table name, View name or SELECT instruction to extract the values
     */
    private string $source;

    /**
     * Collection of all fields used in the source
     * @var Fields|Field[]
     */
    private $fields;

    /**
     * Collection of rules
     * @var Filters|Filter[]
     */
    private $filters;

    /**
     * Collection of columns
     */
    private array $columns;

    /**
     * Collection of rows
     */
    private array $rows;

    /**
     * Collection of aggregates
     * @var Aggregates|Aggregate[]
     */
    private $aggregates;

    /**
     * Information of extended information
     */
    private array $info;

    public function __construct($source = '')
    {
        $this->fields = new Fields();
        $this->aggregates = new Aggregates();
        $this->filters = new Filters();
        $this->rows = [];
        $this->columns = [];
        $this->info = [];
        $this->clearInfo();
        $this->setSource($source);
    }

    public function __clone()
    {
        // Force a copy of this->object, otherwise  it will point to same object.
        if (null !== $this->fields) {
            $this->fields = clone $this->fields;
        }
        if (null !== $this->filters) {
            $this->filters = clone $this->filters;
        }
        if (null !== $this->aggregates) {
            $this->aggregates = clone $this->aggregates;
        }
    }

    public function setSource(string $source)
    {
        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Return a current value of information
     * If keyword not found return FALSE
     * If no keyword provided then it returns the complete array of values
     *
     * @param string $keyword
     * @return mixed
     */
    public function getInfo($keyword = '')
    {
        if (! $keyword) {
            return $this->info;
        }
        if (array_key_exists($keyword, $this->info)) {
            return $this->info[$keyword];
        }
        return false;
    }

    /**
     * @param scalar|\Stringable|null $value
     */
    public function setInfo(string $keyword, $value)
    {
        if (array_key_exists($keyword, $this->info)) {
            $this->info[$keyword] = (string) $value;
        }
    }

    public function clearSelectors()
    {
        $this->clearSelectorRows();
        $this->clearSelectorColumns();
        $this->clearFilters();
        $this->clearAggregates();
    }

    public function clearSelectorRows()
    {
        $this->rows = [];
    }

    public function clearFilters()
    {
        $this->filters->clear();
    }

    public function clearAggregates()
    {
        $this->aggregates->clear();
    }

    public function clearSelectorColumns()
    {
        $this->columns = [];
    }

    public function clearInfo()
    {
        $this->info = [
            'author' => '',
            'created' => date('Y-m-d H:i:s', time()),
            'description' => '',
            'protected' => 'no',
        ];
    }

    public function reset()
    {
        $this->clearInfo();
        $this->fields->clear();
        $this->clearSelectors();
    }

    public function addSourceField($fieldname, $caption, $type)
    {
        $this->fields->addItem(new Field($fieldname, $caption, $type));
    }

    public function addFilter($fieldname, $operator, $arguments)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Field $fieldname does not exists");
        }
        $this->filters->addItem(new Filter($fieldname, $operator, $arguments));
    }

    public function addColumn($fieldname)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Invalid field name $fieldname");
        }
        if (! in_array($fieldname, $this->columns)) {
            $this->columns[] = $fieldname;
        }
    }

    public function addRow($fieldname)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Invalid field name $fieldname");
        }
        if (! in_array($fieldname, $this->rows)) {
            $this->rows[] = $fieldname;
        }
    }

    /**
     * @param string $fieldname
     * @param string $asname
     * @param string $caption
     * @param string $agregatorfunction
     * @param int $decimals
     * @param string $order
     */
    public function addAggregate(
        $fieldname,
        $asname,
        $caption,
        $agregatorfunction,
        $decimals = 2,
        $order = Aggregate::ORDERNONE
    ) {
        static $counter = 0;
        if ('' === $asname) {
            $asname = '__agregator_' . (++$counter);
        }
        $this->aggregates->addItem(new Aggregate($fieldname, $asname, $caption, $agregatorfunction, $decimals, $order));
    }

    public function getSourceFields()
    {
        return $this->fields->asArray();
    }

    public function getCurrentFilters()
    {
        $return = $this->filters->asArray();
        foreach ($return as $i => $value) {
            $return[$i]['caption'] = $this->getFieldElement($value['fieldname'])->getCaption();
        }
        return $return;
    }

    public function getCurrentColumns()
    {
        $return = [];
        foreach ($this->columns as $column) {
            $field = $this->getFieldElement($column);
            $return[] = [
                'caption' => $field->getCaption(),
                'fieldname' => $field->getFieldname(),
            ];
        }
        return $return;
    }

    public function getCurrentRows()
    {
        $return = [];
        foreach ($this->rows as $row) {
            $field = $this->getFieldElement($row);
            $return[] = [
                'caption' => $field->getCaption(),
                'fieldname' => $field->getFieldname(),
            ];
        }
        return $return;
    }

    public function hasColumns()
    {
        return count($this->columns) > 0;
    }

    public function hasRows()
    {
        return count($this->rows) > 0;
    }

    public function getCurrentAggregates()
    {
        return $this->aggregates->asArray();
    }

    /**
     * Return an field object from the field list
     * @param string $fieldname
     * @return Field
     * @throws PivotException when field name does not exists
     */
    public function getFieldElement($fieldname)
    {
        if (! $this->fields->exists($fieldname)) {
            throw new PivotException("Expected to find field $fieldname but it does not exists in the list");
        }
        return $this->fields->value($fieldname);
    }

    public function getFieldsCollection()
    {
        return $this->fields;
    }

    public function getAggregatesCollection()
    {
        return $this->aggregates;
    }

    public function getFiltersCollection()
    {
        return $this->filters;
    }
}
