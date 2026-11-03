<?php

declare(strict_types=1);

namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Aggregate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AggregateTest extends TestCase
{
    public function testConstructor(): void
    {
        $fieldName = 'foo';
        $asName = 'bar';
        $caption = 'The foo';
        $group = Aggregate::AVG;
        $decimals = 2;
        $order = Aggregate::ORDERASC;
        $asArray = [
            'fieldname' => $fieldName,
            'asname' => $asName,
            'caption' => $caption,
            'group' => $group,
            'decimals' => $decimals,
            'order' => $order,
        ];
        $filter = new Aggregate(
            $fieldName,
            $asName,
            $caption,
            $group,
            $decimals,
            $order
        );
        $this->assertEquals($fieldName, $filter->getFieldName());
        $this->assertEquals($asName, $filter->getAsName());
        $this->assertEquals($caption, $filter->getCaption());
        $this->assertEquals($group, $filter->getGroup());
        $this->assertEquals($decimals, $filter->getDecimals());
        $this->assertEquals($order, $filter->getOrder());
        $this->assertEquals($asArray, $filter->asArray());
    }

    public function testConstructorWithInvalidGroup(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('group');

        new Aggregate('field', 'as', 'caption', 'XXX', 2, Aggregate::ORDERDESC);
    }

    public function testConstructorWithInvalidOrder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('order');

        new Aggregate('field', 'as', 'caption', Aggregate::MAX, 2, 'FOO');
    }

    public function testConstructorWithInvalidDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('decimals');

        new Aggregate('', 'as', 'caption', Aggregate::MAX, -1, Aggregate::ORDERDESC);
    }

    public function testStaticGroupTypes(): void
    {
        $ops = Aggregate::groupTypes();
        $this->assertContains(Aggregate::AVG, $ops);
        $this->assertNotContains('FOO', $ops);
    }

    public function testStaticOperatorExists(): void
    {
        $this->assertFalse(Aggregate::groupExists('FOO'));
        $this->assertTrue(Aggregate::groupExists(Aggregate::AVG));
    }
}
