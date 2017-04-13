<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Aggregate;
use PHPUnit\Framework\TestCase;

class AggregateTest extends TestCase
{
    public function testConstructor()
    {
        $fieldname = 'foo';
        $asname = 'bar';
        $caption = 'The foo';
        $group = Aggregate::AVG;
        $decimals = 2;
        $order = Aggregate::ORDERASC;
        $asArray = [
            'fieldname' => $fieldname,
            'asname' => $asname,
            'caption' => $caption,
            'group' => $group,
            'decimals' => $decimals,
            'order' => $order,
        ];
        $filter = new Aggregate(
            $fieldname,
            $asname,
            $caption,
            $group,
            $decimals,
            $order
        );
        $this->assertEquals($fieldname, $filter->getFieldname());
        $this->assertEquals($asname, $filter->getAsname());
        $this->assertEquals($caption, $filter->getCaption());
        $this->assertEquals($group, $filter->getGroup());
        $this->assertEquals($decimals, $filter->getDecimals());
        $this->assertEquals($order, $filter->getOrder());
        $this->assertEquals($asArray, $filter->asArray());
    }

    public function testConstructorWithInvalidGroup()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('group');

        new Aggregate('field', 'as', 'caption', 'XXX', 2, '');
    }

    public function testConstructorWithInvalidOrder()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('order');

        new Aggregate('field', 'as', 'caption', Aggregate::MAX, 2, 'FOO');
    }

    public function testConstructorWithInvalidDecimals()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('decimals');

        new Aggregate('', 'as', 'caption', Aggregate::MAX, -1, 'FOO');
    }

    public function testStaticGroupTypes()
    {
        $ops = Aggregate::groupTypes();
        $this->assertInternalType('array', $ops);
        $this->assertContains(Aggregate::AVG, $ops);
        $this->assertNotContains('FOO', $ops);
    }

    public function testStaticOperatorExists()
    {
        $this->assertFalse(Aggregate::groupExists('FOO'));
        $this->assertTrue(Aggregate::groupExists(Aggregate::AVG));
    }
}
