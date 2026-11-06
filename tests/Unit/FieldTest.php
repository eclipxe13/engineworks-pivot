<?php

declare(strict_types=1);

namespace EngineWorks\Pivot\Tests\Unit;

use EngineWorks\Pivot\Field;
use EngineWorks\Pivot\Tests\TestCase;
use InvalidArgumentException;

final class FieldTest extends TestCase
{
    public function testConstruct(): void
    {
        $fieldName = 'foo';
        $caption = 'bar';
        $type = Field::TEXT;
        $asArray = [
            'fieldname' => $fieldName,
            'caption' => $caption,
            'type' => $type,
        ];

        $field = new Field($fieldName, $caption, $type);

        $this->assertSame($fieldName, $field->getFieldName());
        $this->assertSame($caption, $field->getCaption());
        $this->assertSame($type, $field->getType());
        $this->assertEquals($asArray, $field->asArray());
    }

    public function testConstructWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('type');

        new Field('', '', 'Foo');
    }
}
