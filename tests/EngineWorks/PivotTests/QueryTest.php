<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotException;
use EngineWorks\Pivot\PivotFile;
use EngineWorks\Pivot\Query;
use EngineWorks\Pivot\QueryResult;
use EngineWorks\Pivot\Result;
use EngineWorks\PivotTests\Utils\DbConnection;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testQuery()
    {
        $filename = __DIR__ . '/assets/pivot-file-base.xml';
        $pivot = $this->openPivotFile($filename);
        $query = new Query(DbConnection::db(), $pivot);
        $queryResult = $query->query();
        $this->assertInstanceOf(QueryResult::class, $queryResult);
        $totals = $queryResult->getTotals();
        $details = $queryResult->getDetails();
        $this->assertTrue($queryResult->hasDetails());
        $this->assertInstanceOf(Result::class, $totals);
        $this->assertInstanceOf(Result::class, $details);
    }

    public function providerQueryAndCompareWithJson()
    {
        return [
            ['total'],
            ['rows-zones'],
            ['columns-periods'],
            ['matrix-zones-periods'],
            ['matrix-zones-sales-periods'],
            ['rows-zones-sales-ordered'],
            ['rows-customers-filtered'],
        ];
    }

    /**
     * @dataProvider providerQueryAndCompareWithJson
     * @param $testName
     */
    public function testQueryAndCompareWithJson($testName)
    {
        // pivot file
        $pivotFile = __DIR__ . '/assets/query-' . $testName . '.xml';
        $this->assertFileExists($pivotFile);

        // open pivot file
        $pivot = $this->openPivotFile($pivotFile);
        $query = new Query(DbConnection::db(), $pivot);
        $queryResult = $query->query();

        // get queryArray as a known array structure
        $queryArray = (new Utils\QueryResultAsArray())->toArray($queryResult);

        // compare the struct versus a known json file
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/assets/query-' . $testName . '.json',
            json_encode($queryArray, JSON_PRETTY_PRINT)
        );
    }

    public function testQueryThrowsExceptionIfNoFieldsDefined()
    {
        $query = new Query(DbConnection::db(), new Pivot('foo'));

        $this->expectException(PivotException::class);
        $this->expectExceptionMessage('Nothing to query');

        $query->query();
    }

    private function openPivotFile($pivotFile)
    {
        $pivot = new Pivot();
        (new PivotFile($pivot))->open($pivotFile);
        return $pivot;
    }
}
