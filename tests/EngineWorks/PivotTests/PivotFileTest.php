<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Aggregate;
use EngineWorks\Pivot\Field;
use EngineWorks\Pivot\Filter;
use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotException;
use EngineWorks\Pivot\PivotFile;
use EngineWorks\PivotTests\Utils\DbConnection;
use PHPUnit\Framework\TestCase;

class PivotFileTest extends TestCase
{
    private function createPivot()
    {
        return new Pivot(DbConnection::db());
    }

    private function createPivotWithStruct()
    {
        $pivot = $this->createPivot();
        // source
        $pivot->setSource('records');
        // sourcefields
        $pivot->addSourceField('period', 'Customer', Field::TEXT);
        $pivot->addSourceField('customerid', 'Customer ID', Field::TEXT);
        $pivot->addSourceField('customername', 'Customer', Field::TEXT);
        $pivot->addSourceField('salesid', 'Agent ID', Field::TEXT);
        $pivot->addSourceField('salesname', 'Sales Agent', Field::TEXT);
        $pivot->addSourceField('zoneid', 'Zone ID', Field::TEXT);
        $pivot->addSourceField('zonename', 'Zone', Field::TEXT);
        $pivot->addSourceField('partnumber', 'SKU', Field::TEXT);
        $pivot->addSourceField('description', 'Product', Field::TEXT);
        $pivot->addSourceField('quantity', 'Quantity', Field::INT);
        $pivot->addSourceField('unitprice', 'Price', Field::NUMBER);
        $pivot->addSourceField('unitcost', 'Unit Cost', Field::NUMBER);
        $pivot->addSourceField('totalsale', 'Sale', Field::NUMBER);
        $pivot->addSourceField('totalcost', 'Cost', Field::NUMBER);
        // filters
        $pivot->addFilter('period', Filter::IN, ['201701', '201702']);
        $pivot->addFilter('unitprice', Filter::GRATER, 0);
        // rows
        $pivot->addRow('zonename');
        $pivot->addRow('salesname');
        // columns
        $pivot->addColumn('period');
        // aggregates
        $pivot->addAggregate('totalcost', 'sum_totalcost', 'Costs', Aggregate::SUM, 2, Aggregate::ORDERNONE);
        $pivot->addAggregate('totalsale', 'sum_totalsale', 'Sales', Aggregate::SUM, 2, Aggregate::ORDERDESC);
        $pivot->setInfo('author', 'Mr Foo Bar');
        $pivot->setInfo('created', '2017-04-12 16:54:58');
        $pivot->setInfo('description', 'A test for save');

        return $pivot;
    }

    public function testConstruct()
    {
        $pivot = $this->createPivot();
        $pf = new PivotFile($pivot);
        $this->assertSame($pivot, $pf->getPivot());
        $this->assertSame('', $pf->getFilename());
    }

    public function testSaveAs()
    {
        $pivot = $this->createPivotWithStruct();
        $temporalFile = tempnam(null, null);

        $pf = new PivotFile($pivot);
        $pf->saveAs($temporalFile, true);
        $this->assertEquals($temporalFile, $pf->getFilename());

        $this->assertFileExists($temporalFile);
        $this->assertXmlFileEqualsXmlFile(__DIR__ . '/assets/pivot-file-base.xml', $temporalFile);

        unlink($temporalFile);
    }

    public function testOpen()
    {
        $pivot = $this->createPivot();
        $file = __DIR__ . '/assets/pivot-file-base.xml';

        $pf = new PivotFile($pivot);
        $pf->open($file);
        $this->assertEquals($file, $pf->getFilename());

        $expected = $this->createPivotWithStruct();
        $this->assertEquals($expected, $pivot);
    }

    public function testSaveThrowsExeptionIfNotFilename()
    {
        $pf = new PivotFile($this->createPivot());

        $this->expectException(PivotException::class);
        $this->expectExceptionMessage('Cannot save a the pivot file without a file name');

        $pf->save();
    }

    public function testSave()
    {
        // copy base file to temporal
        $temporalFile = tempnam(null, null);
        copy(__DIR__ . '/assets/pivot-file-base.xml', $temporalFile);

        // open temporal
        $pivot = $this->createPivot();
        $pf = new PivotFile($pivot);
        $pf->open($temporalFile);

        // change pivot
        $pivot->clearFilters();
        $pf->save();

        // compare changed file
        $this->assertXmlFileEqualsXmlFile(__DIR__ . '/assets/pivot-file-changed.xml', $temporalFile);

        // clear
        unlink($temporalFile);
    }
}
