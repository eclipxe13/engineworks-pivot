<?php
namespace EngineWorks\PivotTests\Formatters;

use EngineWorks\Pivot\Formatters\XhtmlTable;
use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotFile;
use EngineWorks\PivotTests\Utils\DbConnection;
use PHPUnit\Framework\TestCase;

class XhtmlTableTest extends TestCase
{
    public function testConstructor()
    {
        $xhtmlTable = new XhtmlTable();
        $options = $xhtmlTable->defaultOptions();

        $this->assertEquals($options, $xhtmlTable->getOptions());
    }

    public function testConstructorWithOptions()
    {
        $options = $this->getFullOptions();
        $xhtmlTable = new XhtmlTable($options);

        $this->assertEquals($options, $xhtmlTable->getOptions());
    }

    public function testGetSetGetOption()
    {
        $xhtmlTable = new XhtmlTable();

        $this->assertEquals('', $xhtmlTable->getOption('table-id'));

        $xhtmlTable->setOption('table-id', 'foo');

        $this->assertEquals('foo', $xhtmlTable->getOption('table-id'));
    }

    public function testPivotExport()
    {
        $pivotFile = __DIR__ . '/../assets/xhtml-pivot.xml';
        $xhtmlFile = __DIR__ . '/../assets/xhtml-expected.xml';

        // open pivot file
        $pivot = new Pivot(DbConnection::db());
        (new PivotFile($pivot))->open($pivotFile);
        $queryResult = $pivot->query();

        $xhtmlTable = new XhtmlTable($this->getFullOptions());
        $xhtmlContent = $xhtmlTable->asXhtmlTable($queryResult);

        $this->assertXmlStringEqualsXmlFile($xhtmlFile, $xhtmlContent);
    }

    private function getFullOptions()
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
