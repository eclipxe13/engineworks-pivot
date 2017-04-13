<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Field;
use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotException;
use EngineWorks\Pivot\PivotFile;
use EngineWorks\Pivot\QueryResult;
use EngineWorks\Pivot\Result;
use EngineWorks\PivotTests\Utils\DbConnection;
use PHPUnit\Framework\TestCase;

class PivotTest extends TestCase
{
    private function createPivot($source)
    {
        return new Pivot(DbConnection::db(), $source);
    }
    public function testConstructor()
    {
        $pivot = new Pivot(DbConnection::db());
        $this->assertEmpty($pivot->getSource());
        $this->assertNotNull($pivot->getSource());
        $this->assertSame('', $pivot->getInfo('author'));
        $this->assertNotEmpty($pivot->getInfo('created'));
        $this->assertSame('', $pivot->getInfo('description'));
        $this->assertSame('no', $pivot->getInfo('protected'));
        $this->assertSame([], $pivot->getSourceFields());
    }

    public function testConstructorWithSource()
    {
        $source = '...';
        $pivot = $this->createPivot($source);
        $this->assertSame($source, $pivot->getSource());
    }

    public function testQueryThrowsExceptionIfNoFieldsDefined()
    {
        $source = 'vw_devsummary';
        $pivot = $this->createPivot($source);

        $this->expectException(PivotException::class);
        $this->expectExceptionMessage('Nothing to query');
        $pivot->query();
    }

    public function testSourceFieldsAddGet()
    {
        $source = 'vw_devsummary';
        $sourceFields = [
            [
                'fieldname' => 'devolutionyear',
                'caption' => 'Year',
                'type' => Field::NUMBER,
            ],
            [
                'fieldname' => 'clientname',
                'caption' => 'Client',
                'type' => Field::TEXT,
            ],
        ];
        $pivot = $this->createPivot($source);
        foreach ($sourceFields as $sourceField) {
            $pivot->addSourceField($sourceField['fieldname'], $sourceField['caption'], $sourceField['type']);
        }
        $this->assertEquals($sourceFields, $pivot->getSourceFields());
    }

    public function testQuery()
    {
        $filename = __DIR__ . '/assets/pivot-file-base.xml';
        $pivot = $this->createPivot('');
        $opener = new PivotFile($pivot);
        $opener->open($filename);
        $queryResult = $pivot->query();
        $this->assertInstanceOf(QueryResult::class, $queryResult);
        $totals = $queryResult->getTotals();
        $details = $queryResult->getDetails();
        $this->assertTrue($queryResult->hasDetails());
        $this->assertInstanceOf(Result::class, $totals);
        $this->assertInstanceOf(Result::class, $details);
    }

    public function testQueryTotal()
    {
        $this->queryAndCompareWithJson('total');
    }

    public function testQueryRowsZones()
    {
        $this->queryAndCompareWithJson('rows-zones');
    }

    public function testQueryColumnsPeriods()
    {
        $this->queryAndCompareWithJson('columns-periods');
    }

    public function testQueryMatrixZonesPeriods()
    {
        $this->queryAndCompareWithJson('matrix-zones-periods');
    }

    public function testQueryMatrixZonesSalesPeriods()
    {
        $this->queryAndCompareWithJson('matrix-zones-sales-periods');
    }

    public function testQueryRowsZonesSalesOrdered()
    {
        $this->queryAndCompareWithJson('rows-zones-sales-ordered');
    }

    public function testQueryRowsCustomersFiltered()
    {
        $this->queryAndCompareWithJson('rows-customers-filtered');
    }

    public function queryAndCompareWithJson($testName)
    {
        // pivot file
        $pivotFile = __DIR__ . '/assets/query-' . $testName . '.xml';
        $this->assertFileExists($pivotFile);

        // open pivot file
        $pivot = $this->createPivot('');
        (new PivotFile($pivot))->open($pivotFile);
        $queryResult = $pivot->query();

        // get queryArray as a known array structure
        $queryArray = (new Utils\QueryResultAsArray())->toArray($queryResult);

        // compare the struct versus a known json file
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/assets/query-' . $testName . '.json',
            json_encode($queryArray, JSON_PRETTY_PRINT)
        );
    }
}
