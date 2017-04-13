<?php
 declare(strict_types = 1);
namespace EngineWorks\Pivot\Formatters;

use EngineWorks\Pivot\QueryResult;
use EngineWorks\Pivot\Result;
use EngineWorks\Pivot\Utils;
use SimpleXMLElement;

class XhtmlTable
{
    /** @var  array */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $this->defaultOptions();
        $this->setOptions($options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption(string $option) : string
    {
        return $this->options[$option];
    }

    public function setOptions(array $options)
    {
        foreach (array_keys($this->options) as $option) {
            if (is_string($option) && isset($options[$option]) && is_string($options[$option])) {
                $this->options[$option] = $options[$option];
            }
        }
    }

    public function setOption(string $option, string $value)
    {
        if (array_key_exists($option, $this->options)) {
            $this->options[$option] = $value;
        }
    }

    public function defaultOptions()
    {
        return [
            'table-id' => '',
            'table-class' => '',
            'row-class' => '',
            'total-class' => '',
            'subtotal-class' => '',
            'column-total-caption' => 'Total',
            'values-caption' => 'Values',
            'row-total-caption' => 'Total',
        ];
    }

    public function asXhtmlTable(QueryResult $queryResult) : string
    {
        return $this->asSimpleXml($queryResult)->asXML();
    }

    /**
     * @param QueryResult $queryResult
     * @return SimpleXMLElement
     */
    public function asSimpleXml(QueryResult $queryResult) : SimpleXMLElement
    {
        /** @var Result $totals */
        $totals = $queryResult->getTotals();
        /** @var Result $details */
        $details = ($queryResult->hasDetails()) ? $queryResult->getDetails() : null;

        //
        // internal variables
        //
        $aggregates = $queryResult->getAggregates();
        $valuesCount = count($aggregates);
        $colsDepth = 0;
        $colsTree = null;
        /* @var $colsLastLevel Result[] */
        $colsLastLevel = [];
        $columns = [];
        if ($details != null) {
            // setup the columns information
            $columns = $queryResult->getColumns();
            $colsDepth = count($columns); // $details->getDepth(false);
            $colsTree = $details->copy($colsDepth);
            $colsLastLevel = $colsTree->getLastChildrenArray();
        }

        // make the table
        // use LIBXML_NOXMLDECL to avoid xml declaration on asXml()
        $table = new SimpleXMLElement('<' . 'table/>', LIBXML_NOXMLDECL);
        if ($this->getOption('table-class')) {
            $table->addAttribute('class', $this->getOption('table-class'));
        }
        if ($this->getOption('table-id')) {
            $table->addAttribute('id', $this->getOption('table-id'));
        }

        // make the thead
        $thead = $table->addChild('thead');
        if ($colsDepth) {
            // build the rows inside thead
            foreach ($columns as $column) {
                $thead->addChild('tr')->addChild('th', Utils::escapeXml($column['caption']));
            }
            // make header if the column details
            foreach ($colsTree->children as $item) {
                $this->xhtmlAddHeader($thead, $item, 1, $valuesCount);
            }
            if (isset($thead->tr) and count($thead->tr) > 0) {
                $tr = $thead->tr[0];
                $th = $tr->addChild('th', Utils::escapeXml($this->getOption('column-total-caption')));
                if ($valuesCount > 1) {
                    $th->addAttribute('colspan', (string) $valuesCount);
                }
                if ($colsDepth > 0) {
                    $th->addAttribute('rowspan', (string) $colsDepth);
                }
            }
        }

        // make the row of the details
        if (count($aggregates) > 1 or count($columns) == 0) {
            $tr = $thead->addChild('tr');
            $tr->addChild('th', Utils::escapeXml($this->getOption('values-caption')));
            $countColsLastLevel = count($colsLastLevel);
            for ($i = 0; $i <= $countColsLastLevel; $i++) {
                foreach ($aggregates as $aggregate) {
                    $tr->addChild('th', Utils::escapeXml($aggregate['caption']));
                }
            }
        }

        // tfoot
        $tfoot = $table->addChild('tfoot');
        $totals->caption = $this->getOption('row-total-caption');
        $this->xhtmlAddRow($tfoot, $totals, $aggregates, $details, $colsLastLevel, true);

        // tbody
        $tbody = $table->addChild('tbody');
        foreach ($totals->children as $result) {
            $this->xhtmlAddRow($tbody, $result, $aggregates, $details, $colsLastLevel, false);
        }
        return $table;
    }

    /**
     * @param SimpleXMLElement $thead
     * @param Result $item
     * @param $level
     * @param $valuesCount
     */
    private function xhtmlAddHeader(SimpleXMLElement $thead, Result $item, $level, $valuesCount)
    {
        // find the current tr according to level
        $i = 1;
        $tr = null;
        foreach ($thead->children() as $tr) {
            if ($i == $level) {
                break;
            }
            $i++;
        }
        if ($tr instanceof SimpleXMLElement) {
            // insert the current th
            $th = $tr->addChild('th', Utils::escapeXml($item->caption));
            $th->addAttribute('colspan', (string) max(1, $item->getHorizontalDepth() * $valuesCount));
            foreach ($item->children as $child) {
                $this->xhtmlAddHeader($thead, $child, $level + 1, $valuesCount);
            }
        }
    }

    /**
     * @param SimpleXMLElement $tbody
     * @param Result $result
     * @param array $aggregates
     * @param Result $details
     * @param Result[] $colsLastLevel
     * @param bool $istotal
     */
    private function xhtmlAddRow(
        SimpleXMLElement $tbody,
        Result $result,
        array $aggregates,
        $details,
        $colsLastLevel,
        $istotal
    ) {
        $tr = $tbody->addChild('tr');
        $rowclasses = [];
        if ($this->getOption('row-class')) {
            $rowclasses[] = $this->getOption('row-class');
        }
        if ($istotal and $this->getOption('total-class')) {
            $rowclasses[] = $this->getOption('total-class');
        }
        if (! $istotal and $this->getOption('subtotal-class') and $result->hasChildren()) {
            $rowclasses[] = $this->getOption('subtotal-class');
        }
        if (count($rowclasses)) {
            $tr->addAttribute('class', implode(' ', $rowclasses));
        }
        $caption = $tr->addChild('th');
        $currentDepth = $result->getCurrentDepth() - 1;
        while ($currentDepth > 0) {
            $currentDepth--;
            $caption = $caption->addChild('div');
        }
        // $caption = $caption->addChild("div", Utils::escapeXml($result->caption));
        $caption->addChild('div', Utils::escapeXml($result->caption));
        if ($details != null) {
            $rowPath = $result->getPath();
            foreach ($colsLastLevel as $column) {
                $this->xhtmlAddCellsValues(
                    $tr,
                    $aggregates,
                    $details->searchValue(array_merge($column->getPath(), $rowPath))
                );
            }
        }
        $this->xhtmlAddCellsValues($tr, $aggregates, $result->values);
        if (! $istotal) {
            foreach ($result->children as $item) {
                $this->xhtmlAddRow($tbody, $item, $aggregates, $details, $colsLastLevel, false);
            }
        }
    }

    /**
     * @param SimpleXMLElement $tr
     * @param array $aggregates
     * @param array|null $values
     */
    private function xhtmlAddCellsValues(SimpleXMLElement $tr, array $aggregates, $values)
    {
        if (! is_array($values)) {
            $values = [];
        }
        foreach ($aggregates as $aggregate) {
            $value = '';
            if (isset($values[$aggregate['asname']]) and ! is_null(isset($values[$aggregate['asname']]))) {
                $value = Utils::escapeXml((string) $values[$aggregate['asname']]);
            }
            $tr->addChild('td', $value);
        }
    }
}
