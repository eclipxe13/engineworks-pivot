<?php

declare(strict_types=1);

namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Filter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    public function testConstructor(): void
    {
        $fieldName = 'foo';
        $operator = Filter::EQUAL;
        $arguments = 'bar';
        $asArray = [
            'fieldname' => $fieldName,
            'operator' => $operator,
            'arguments' => $arguments,
        ];
        $filter = new Filter($fieldName, $operator, $arguments);
        $this->assertSame($fieldName, $filter->getFieldName());
        $this->assertSame($operator, $filter->getOperator());
        $this->assertEquals($arguments, $filter->getArguments());
        $this->assertEquals($asArray, $filter->asArray());
    }

    public function testConstructorWithInvalidOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('operator');

        new Filter('field', 'FOO', '');
    }

    public function testConstructorWithInvalidArgumentNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('argument');

        new Filter('field', Filter::EQUAL, null);
    }

    public function testConstructorWithInvalidArgumentNoArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be an array');

        new Filter('field', Filter::EQUAL, []);
    }

    public function testConstructorWithInvalidArgumentOnlyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an array');

        new Filter('field', Filter::IN, '');
    }

    public function testStaticOperators(): void
    {
        $ops = Filter::operators();
        $this->assertContains(Filter::EQUAL, $ops);
        $this->assertContains(Filter::GREATER, $ops);
        $this->assertNotContains('FOO', $ops);
    }

    public function testStaticOperatorExists(): void
    {
        $this->assertFalse(Filter::operatorExists('FOO'));
        $this->assertTrue(Filter::operatorExists(Filter::GREATEREQUAL));
    }
}
