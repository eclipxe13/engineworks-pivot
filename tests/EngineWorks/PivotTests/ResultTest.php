<?php

declare(strict_types=1);

namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Result;
use EngineWorks\Pivot\Results;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    public function testConstruct(): void
    {
        $result = new Result('foo', 'Foo');

        $this->assertEquals('foo', $result->fieldName);
        $this->assertEquals('Foo', $result->caption);
        $this->assertNull($result->values);
        $this->assertTrue($result->isRow);
        $this->assertInstanceOf(Results::class, $result->getChildren());
        $this->assertNull($result->getParent());
    }

    public function testSetParentOnlyOnce(): void
    {
        $parent = new Result('parent', 'Parent');
        $result = new Result('foo', 'Foo');

        $result->setParent($parent);
        $this->assertSame($parent, $result->getParent());

        $this->expectException(\EngineWorks\Pivot\PivotException::class);
        $this->expectExceptionMessage('parent has been set already');

        $result->setParent($parent);
    }

    public function testChildrenAreCreatedOnce(): void
    {
        $result = new Result('foo', 'Foo');

        $children = $result->getChildren();
        $this->assertSame($children, $result->getChildren());
    }
}
