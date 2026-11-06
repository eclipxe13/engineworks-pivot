<?php

declare(strict_types=1);

namespace EngineWorks\Pivot\Formatters;

use EngineWorks\Pivot\QueryResult;
use EngineWorks\Pivot\Result;
use EngineWorks\Pivot\Utils;
use SimpleXMLElement;

class XhtmlTable
{
    /** @var array<string, string> */
    private array $options;

    /** @param array<string, string> $options */
    public function __construct(array $options = [])
    {
        $this->options = $this->defaultOptions();
        $this->setOptions($options);
    }

    /** @return array<string, string> */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $option): string
    {
        return $this->options[$option];
    }

    /** @param array<string, mixed> $options */
    public function setOptions(array $options): void
    {
        foreach (array_keys($this->options) as $option) {
            if (isset($options[$option]) && is_string($options[$option])) {
                $this->options[$option] = $options[$option];
            }
        }
    }

    public function setOption(string $option, string $value): void
    {
        if (array_key_exists($option, $this->options)) {
            $this->options[$option] = $value;
        }
    }

    /** @return array<string, string> */
    public function defaultOptions(): array
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

    public function asXhtmlTable(QueryResult $queryResult): string
    {
        $content = $this->asSimpleXml($queryResult)->asXML();
        return false === $content ? '' : $content;
    }

    public function asSimpleXml(QueryResult $queryResult): SimpleXMLElement
    {
        $totals = $queryResult->getTotals();
        $details = $queryResult->hasDetails() ? $queryResult->getDetails() : null;

        // internal variables
        $aggregates = $queryResult->getAggregates();
        $valuesCount = count($aggregates);
        $colsDepth = 0;
        $colsTree = new Result('_', '_'); // dummy object
        /** @var list<array{caption: string, fieldname: string}> $columns */
        $columns = [];
        /** @var list<Result> $colsLastLevel */
        $colsLastLevel = [];
        if (null !== $details) {
            // set up the columns information
            $columns = $queryResult->getColumns();
            // use count of columns instead of calling $details->getDepth(false);
            $colsDepth = count($columns);
            $colsTree = $details->copy($colsDepth);
            $colsLastLevel = $colsTree->getLastChildrenArray();
        }

        // make the table
        // use LIBXML_NOXMLDECL to avoid xml declaration on asXml()
        $table = new SimpleXMLElement('<table/>', LIBXML_NOXMLDECL);
        if ('' !== $this->getOption('table-class')) {
            $table->addAttribute('class', $this->getOption('table-class'));
        }
        if ('' !== $this->getOption('table-id')) {
            $table->addAttribute('id', $this->getOption('table-id'));
        }

        // make the thead
        $thead = $table->addChild('thead');
        if ($colsDepth > 0) {
            // build the rows inside thead
            foreach ($columns as $column) {
                $thead->addChild('tr')->addChild('th', Utils::escapeXml($column['caption']));
            }
            // make header if the column details
            foreach ($colsTree->getChildren() as $item) {
                $this->xhtmlAddHeader($thead, $item, 1, $valuesCount);
            }
            $trs = $thead->tr;
            if ($trs instanceof SimpleXMLElement && count($trs) > 0) {
                $tr = $trs[0];
                if ($tr instanceof SimpleXMLElement) {
                    $th = $tr->addChild('th', Utils::escapeXml($this->getOption('column-total-caption')));
                    if ($valuesCount > 1) {
                        $th->addAttribute('colspan', (string) $valuesCount);
                    }
                    $th->addAttribute('rowspan', (string) $colsDepth);
                }
            }
        }

        // make the row of the details
        if (count($aggregates) > 1 || 0 === count($columns)) {
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
        foreach ($totals->getChildren() as $result) {
            $this->xhtmlAddRow($tbody, $result, $aggregates, $details, $colsLastLevel, false);
        }
        return $table;
    }

    private function xhtmlAddHeader(SimpleXMLElement $thead, Result $item, int $level, int $valuesCount): void
    {
        // find the current tr according to level
        $i = 1;
        $tr = null;
        foreach ($thead->children() as $tr) {
            if ($i === $level) {
                break;
            }
            $i++;
        }
        if ($tr instanceof SimpleXMLElement) {
            // insert the current th
            $th = $tr->addChild('th', Utils::escapeXml($item->caption));
            $th->addAttribute('colspan', (string) max(1, $item->getHorizontalDepth() * $valuesCount));
            foreach ($item->getChildren() as $child) {
                $this->xhtmlAddHeader($thead, $child, $level + 1, $valuesCount);
            }
        }
    }

    /**
     * @param list<array{
     *     fieldname: string,
     *     caption: string,
     *     group: string,
     *     asname: string,
     *     decimals: int,
     *     order: string
     * }> $aggregates
     * @param list<Result> $colsLastLevel
     */
    private function xhtmlAddRow(
        SimpleXMLElement $tbody,
        Result $result,
        array $aggregates,
        Result|null $details,
        array $colsLastLevel,
        bool $isTotal,
    ): void {
        $tr = $tbody->addChild('tr');
        $rowClasses = [];
        if ('' !== $this->getOption('row-class')) {
            $rowClasses[] = $this->getOption('row-class');
        }
        if ($isTotal && '' !== $this->getOption('total-class')) {
            $rowClasses[] = $this->getOption('total-class');
        }
        if (! $isTotal && '' !== $this->getOption('subtotal-class') && $result->hasChildren()) {
            $rowClasses[] = $this->getOption('subtotal-class');
        }
        if (0 !== count($rowClasses)) {
            $tr->addAttribute('class', implode(' ', $rowClasses));
        }
        $caption = $tr->addChild('th');
        $currentDepth = $result->getCurrentDepth() - 1;
        while ($currentDepth > 0) {
            $currentDepth--;
            $caption = $caption->addChild('div');
        }
        $caption->addChild('div', Utils::escapeXml($result->caption));
        if (null !== $details) {
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
        if (! $isTotal) {
            foreach ($result->getChildren() as $item) {
                $this->xhtmlAddRow($tbody, $item, $aggregates, $details, $colsLastLevel, false);
            }
        }
    }

    /**
     * @param list<array{
     *     fieldname: string,
     *     caption: string,
     *     group: string,
     *     asname: string,
     *     decimals: int,
     *     order: string
     * }> $aggregates
     * @param array<string, scalar|null>|null $values
     */
    private function xhtmlAddCellsValues(SimpleXMLElement $tr, array $aggregates, array|null $values): void
    {
        foreach ($aggregates as $aggregate) {
            $value = '';
            $asName = $aggregate['asname'];
            if (null !== $values && isset($values[$asName])) {
                $value = Utils::escapeXml((string) $values[$asName]);
            }
            $tr->addChild('td', $value);
        }
    }
}
