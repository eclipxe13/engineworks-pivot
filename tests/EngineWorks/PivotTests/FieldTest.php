<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testConstruct()
    {
        $fieldname = 'foo';
        $caption = 'bar';
        $type = Field::TEXT;
        $asArray = [
            'fieldname' => $fieldname,
            'caption' => $caption,
            'type' => $type,
        ];

        $field = new Field($fieldname, $caption, $type);

        $this->assertSame($fieldname, $field->getFieldname());
        $this->assertSame($caption, $field->getCaption());
        $this->assertSame($type, $field->getType());
        $this->assertEquals($asArray, $field->asArray());
    }

    public function testConstructWithInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('type');

        new Field('', '', 'Foo');
    }
}
