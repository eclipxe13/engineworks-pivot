<?php
namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Field;
use EngineWorks\Pivot\Pivot;
use PHPUnit\Framework\TestCase;

class PivotTest extends TestCase
{
    private function createPivot($source)
    {
        return new Pivot($source);
    }

    public function testConstructor()
    {
        $pivot = new Pivot();
        $this->assertSame('', $pivot->getSource());
        $this->assertSame('', $pivot->getInfo('author'));
        $this->assertNotEmpty($pivot->getInfo('created'));
        $this->assertSame('', $pivot->getInfo('description'));
        $this->assertSame('no', $pivot->getInfo('protected'));
        $this->assertSame([], $pivot->getSourceFields());
    }

    public function testConstructorWithSource()
    {
        $source = '...';
        $pivot = $this->createPivot($source);
        $this->assertSame($source, $pivot->getSource());
    }

    public function testSourceFieldsAddGet()
    {
        $source = 'vw_devsummary';
        $sourceFields = [
            [
                'fieldname' => 'devolutionyear',
                'caption' => 'Year',
                'type' => Field::NUMBER,
            ],
            [
                'fieldname' => 'clientname',
                'caption' => 'Client',
                'type' => Field::TEXT,
            ],
        ];
        $pivot = $this->createPivot($source);
        foreach ($sourceFields as $sourceField) {
            $pivot->addSourceField($sourceField['fieldname'], $sourceField['caption'], $sourceField['type']);
        }
        $this->assertEquals($sourceFields, $pivot->getSourceFields());
    }
}
