<?php

declare(strict_types=1);

namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Aggregate;
use EngineWorks\Pivot\Field;
use EngineWorks\Pivot\Filter;
use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotException;
use EngineWorks\Pivot\PivotFile;
use PHPUnit\Framework\TestCase;

class PivotFileTest extends TestCase
{
    private function createPivot(): Pivot
    {
        return new Pivot();
    }

    private function createPivotWithStruct(): Pivot
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
        $pivot->addFilter('unitprice', Filter::GREATER, 0);
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

    public function testConstruct(): void
    {
        $pivot = $this->createPivot();
        $pf = new PivotFile($pivot);
        $this->assertSame($pivot, $pf->getPivot());
        $this->assertSame('', $pf->getFilename());
    }

    public function testSaveAs(): void
    {
        $pivot = $this->createPivotWithStruct();
        $temporalFile = tempnam('', '');

        $pf = new PivotFile($pivot);
        $pf->saveAs($temporalFile, true);
        $this->assertEquals($temporalFile, $pf->getFilename());

        $this->assertFileExists($temporalFile);
        $this->assertXmlFileEqualsXmlFile(__DIR__ . '/assets/pivot-file-base.xml', $temporalFile);

        unlink($temporalFile);
    }

    public function testOpen(): void
    {
        $pivot = $this->createPivot();
        $file = __DIR__ . '/assets/pivot-file-base.xml';

        $pf = new PivotFile($pivot);
        $pf->open($file);
        $this->assertEquals($file, $pf->getFilename());

        $expected = $this->createPivotWithStruct();
        $this->assertEquals($expected, $pivot);
    }

    public function testSaveThrowsExeptionIfNotFilename(): void
    {
        $pf = new PivotFile($this->createPivot());

        $this->expectException(PivotException::class);
        $this->expectExceptionMessage('Cannot save the pivot file without a file name.');

        $pf->save();
    }

    public function testSave(): void
    {
        // copy base file to temporal
        $temporalFile = tempnam('', '');
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
