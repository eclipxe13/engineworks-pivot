<?php

declare(strict_types=1);

namespace EngineWorks\Pivot\Tests\Functional;

use EngineWorks\Pivot\Pivot;
use EngineWorks\Pivot\PivotException;
use EngineWorks\Pivot\PivotFile;
use EngineWorks\Pivot\Query;
use EngineWorks\Pivot\QueryResult;
use EngineWorks\Pivot\Result;
use EngineWorks\Pivot\Tests\TestCase;
use EngineWorks\Pivot\Tests\Utils\DbConnection;
use EngineWorks\Pivot\Tests\Utils\QueryResultAsArray;
use PHPUnit\Framework\Attributes\DataProvider;

final class QueryTest extends TestCase
{
    public function testQuery(): void
    {
        $filename = $this->assetPath('pivot-file-base.xml');
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
    public static function providerQueryAndCompareWithJson(): array
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

    #[DataProvider('providerQueryAndCompareWithJson')]
    public function testQueryAndCompareWithJson(string $testName): void
    {
        // pivot file
        $pivotFile = $this->assetPath(sprintf('query-%s.xml', $testName));
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
            $this->assetPath(sprintf('query-%s.json', $testName)),
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
