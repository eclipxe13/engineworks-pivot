<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use SimpleXMLElement;
use Throwable;

class PivotFile
{
    private string $filename;

    public function __construct(private readonly Pivot $pivot)
    {
        $this->filename = '';
    }

    public function open(string $filename): void
    {
        if (! is_file($filename)) {
            throw new PivotException("Pivot file $filename does not exist.");
        }
        try {
            $contents = file_get_contents($filename);
            if (false === $contents) {
                throw new PivotException("Pivot file $filename cannot be read.");
            }
            $xml = new SimpleXMLElement(
                $contents,
                LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET
            );
            $this->fromXML($xml);
            $this->filename = $filename;
        } catch (Throwable $ex) {
            throw new PivotException('Cannot open the pivot file: ' . $ex->getMessage(), 0, $ex);
        }
    }

    public function save(): void
    {
        $filename = $this->getFilename();
        if ('' === $filename) {
            throw new PivotException('Cannot save the pivot file without a file name.');
        }
        $this->saveAs($filename, true);
    }

    public function saveAs(string $filename, bool $override): void
    {
        if (file_exists($filename) && ! $override) {
            throw new PivotException('The pivot file already exists.');
        }
        try {
            $this->toXML()->asXML($filename);
        } catch (Throwable $ex) {
            throw new PivotException('Cannot save the pivot file: ' . $ex->getMessage(), 0, $ex);
        }
        $this->filename = $filename;
    }

    public function getPivot(): Pivot
    {
        return $this->pivot;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Configure the current pivot from a SimpleXMLElement
     */
    private function fromXML(SimpleXMLElement $xml): void
    {
        $this->pivot->reset();
        if (isset($xml->source)) {
            $this->pivot->setSource((string) $xml->source);
        }
        if (isset($xml->sourcefield)) {
            foreach ($xml->sourcefield as $node) {
                $this->pivot->addSourceField(
                    (string) $node['fieldname'],
                    (string) $node['caption'],
                    (string) $node['type']
                );
            }
        }
        if (isset($xml->filter)) {
            foreach ($xml->filter as $node) {
                $operator = (string) $node['operator'];
                if (Filter::IN === $operator) {
                    $arguments = [];
                    if (isset($node->argument)) {
                        foreach ($node->argument as $argument) {
                            $arguments[] = (string) $argument;
                        }
                    }
                } else {
                    $arguments = (isset($node['singleargument'])) ? (string) $node['singleargument'] : '';
                }
                $this->pivot->addFilter((string) $node['fieldname'], $operator, $arguments);
            }
        }
        if (isset($xml->column)) {
            foreach ($xml->column as $node) {
                $this->pivot->addColumn((string) $node['fieldname']);
            }
        }
        if (isset($xml->row)) {
            foreach ($xml->row as $node) {
                $this->pivot->addRow((string) $node['fieldname']);
            }
        }
        if (isset($xml->aggregate)) {
            foreach ($xml->aggregate as $node) {
                $this->pivot->addAggregate(
                    (string) $node['fieldname'],
                    (string) $node['asname'],
                    (string) $node['caption'],
                    (string) $node['group'],
                    (int) $node['decimals'],
                    (string) $node['order']
                );
            }
        }
        if (isset($xml->info)) {
            foreach ($xml->info as $node) {
                $this->pivot->setInfo((string) $node['name'], (string) $node);
            }
        }
    }

    /**
     * Get the pivot information as an xml node
     */
    private function toXML(): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<pivot/>');
        $xml->addChild('source', Utils::escapeXml($this->pivot->getSource()));
        foreach ($this->pivot->getSourceFields() as $item) {
            $node = $xml->addChild('sourcefield');
            $node->addAttribute('fieldname', $item['fieldname']);
            $node->addAttribute('caption', $item['caption']);
            $node->addAttribute('type', $item['type']);
        }
        foreach ($this->pivot->getCurrentFilters() as $item) {
            $node = $xml->addChild('filter');
            $node->addAttribute('fieldname', $item['fieldname']);
            $node->addAttribute('operator', $item['operator']);
            $arguments = $item['arguments'];
            if (is_array($arguments)) {
                foreach ($arguments as $argument) {
                    $node->addChild('argument', Utils::escapeXml(is_scalar($argument) ? (string) $argument : ''));
                }
            } else {
                $node->addAttribute('singleargument', is_scalar($arguments) ? (string) $arguments : '');
            }
        }
        foreach ($this->pivot->getCurrentColumns() as $item) {
            $xml->addChild('column')->addAttribute('fieldname', $item['fieldname']);
        }
        foreach ($this->pivot->getCurrentRows() as $item) {
            $xml->addChild('row')->addAttribute('fieldname', $item['fieldname']);
        }
        foreach ($this->pivot->getCurrentAggregates() as $item) {
            $node = $xml->addChild('aggregate');
            $node->addAttribute('fieldname', $item['fieldname']);
            $node->addAttribute('caption', $item['caption']);
            $node->addAttribute('asname', $item['asname']);
            $node->addAttribute('group', $item['group']);
            $node->addAttribute('decimals', (string) $item['decimals']);
            $node->addAttribute('order', $item['order']);
        }
        // information
        $info = ['description', 'author', 'created'];
        foreach ($info as $name) {
            $value = $this->pivot->getInfo($name);
            $xml->addChild('info', Utils::escapeXml(false === $value ? '' : $value))
                ->addAttribute('name', $name);
        }
        return $xml;
    }
}
