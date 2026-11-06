<?php

declare(strict_types=1);

namespace EngineWorks\Pivot\Tests\Functional\Formatters;

use EngineWorks\Pivot\Formatters\XhtmlTable;
use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotFile;
use EngineWorks\Pivot\Query;
use EngineWorks\Pivot\Tests\TestCase;
use EngineWorks\Pivot\Tests\Utils\DbConnection;

final class XhtmlTableTest extends TestCase
{
    public function testConstructor(): void
    {
        $xhtmlTable = new XhtmlTable();
        $options = $xhtmlTable->defaultOptions();

        $this->assertEquals($options, $xhtmlTable->getOptions());
    }

    public function testConstructorWithOptions(): void
    {
        $options = $this->getFullOptions();
        $xhtmlTable = new XhtmlTable($options);

        $this->assertEquals($options, $xhtmlTable->getOptions());
    }

    public function testGetSetGetOption(): void
    {
        $xhtmlTable = new XhtmlTable();

        $this->assertSame('', $xhtmlTable->getOption('table-id'));

        $xhtmlTable->setOption('table-id', 'foo');

        $this->assertSame('foo', $xhtmlTable->getOption('table-id'));
    }

    public function testPivotExport(): void
    {
        $pivotFile = $this->assetPath('xhtml-pivot.xml');
        $xhtmlFile = $this->assetPath('xhtml-expected.xml');

        // open pivot file
        $pivot = new Pivot();
        (new PivotFile($pivot))->open($pivotFile);
        $query = new Query(DbConnection::db(), $pivot);
        $queryResult = $query->query();

        $xhtmlTable = new XhtmlTable($this->getFullOptions());
        $xhtmlContent = $xhtmlTable->asXhtmlTable($queryResult);

        $this->assertXmlStringEqualsXmlFile($xhtmlFile, $xhtmlContent);
    }

    /** @return array<string, string> */
    private function getFullOptions(): array
    {
        return [
            'table-id' => 'the-table-id',
            'table-class' => 'the-table-class',
            'row-class' => 'the-row-class',
            'total-class' => 'the-total-class',
            'subtotal-class' => 'the-subtotal-class',
            'column-total-caption' => 'Total Caption',
            'values-caption' => 'Values Caption',
            'row-total-caption' => 'Row Total Caption',
        ];
    }
}
