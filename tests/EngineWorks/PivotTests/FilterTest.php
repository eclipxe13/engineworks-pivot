<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testConstructor()
    {
        $fieldname = 'foo';
        $operator = Filter::EQUAL;
        $arguments = 'bar';
        $asArray = [
            'fieldname' => $fieldname,
            'operator' => $operator,
            'arguments' => $arguments,
        ];
        $filter = new Filter($fieldname, $operator, $arguments);
        $this->assertEquals($fieldname, $filter->getFieldname());
        $this->assertEquals($operator, $filter->getOperator());
        $this->assertEquals($arguments, $filter->getArguments());
        $this->assertEquals($asArray, $filter->asArray());
    }

    public function testConstructorWithInvalidOperator()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('operator');

        new Filter('field', 'FOO', '');
    }

    public function testConstructorWithInvalidArgumentNull()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('argument');

        new Filter('field', Filter::EQUAL, null);
    }

    public function testConstructorWithInvalidArgumentNoArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be an array');

        new Filter('field', Filter::EQUAL, []);
    }

    public function testConstructorWithInvalidArgumentOnlyArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an array');

        new Filter('field', Filter::IN, '');
    }

    public function testStaticOperators()
    {
        $ops = Filter::operators();
        $this->assertInternalType('array', $ops);
        $this->assertContains(Filter::EQUAL, $ops);
        $this->assertNotContains('FOO', $ops);
    }

    public function testStaticOperatorExists()
    {
        $this->assertFalse(Filter::operatorExists('FOO'));
        $this->assertTrue(Filter::operatorExists(Filter::EQUAL));
    }
}
