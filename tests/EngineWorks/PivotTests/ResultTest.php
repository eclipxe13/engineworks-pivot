<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Result;
use EngineWorks\Pivot\Results;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    public function testConstruct()
    {
        $result = new Result('foo', 'Foo');

        $this->assertEquals('foo', $result->fieldname);
        $this->assertEquals('Foo', $result->caption);
        $this->assertNull($result->values);
        $this->assertTrue($result->isrow);
        $this->assertInstanceOf(Results::class, $result->children);
        $this->assertNull($result->parent);
    }
}
