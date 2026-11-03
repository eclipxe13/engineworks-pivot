<?php

declare(strict_types=1);

namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotException;
use EngineWorks\Pivot\PivotFile;
use EngineWorks\Pivot\Query;
use EngineWorks\Pivot\QueryResult;
use EngineWorks\Pivot\Result;
use EngineWorks\PivotTests\Utils\DbConnection;
use EngineWorks\PivotTests\Utils\QueryResultAsArray;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testQuery(): void
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

    /**
     * @return list<list<string>>
     */
    public function providerQueryAndCompareWithJson(): array
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
     */
    public function testQueryAndCompareWithJson(string $testName): void
    {
        // pivot file
        $pivotFile = __DIR__ . '/assets/query-' . $testName . '.xml';
        $this->assertFileExists($pivotFile);

        // open pivot file
        $pivot = $this->openPivotFile($pivotFile);
        $query = new Query(DbConnection::db(), $pivot);
        $queryResult = $query->query();

        // get queryArray as a known array structure
        $queryArray = (new QueryResultAsArray())->toArray($queryResult);

        // compare the struct versus a known json file
        $actualJson = json_encode($queryArray, JSON_PRETTY_PRINT);
        if (false === $actualJson) {
            $this->fail('Unable to encode the query result as JSON');
        }
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/assets/query-' . $testName . '.json',
            $actualJson
        );
    }

    public function testQueryThrowsExceptionIfNoFieldsDefined(): void
    {
        $query = new Query(DbConnection::db(), new Pivot('foo'));

        $this->expectException(PivotException::class);
        $this->expectExceptionMessage('Nothing to query');

        $query->query();
    }

    private function openPivotFile(string $pivotFile): Pivot
    {
        $pivot = new Pivot();
        (new PivotFile($pivot))->open($pivotFile);
        return $pivot;
    }
}
