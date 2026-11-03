<?php

declare(strict_types=1);

namespace EngineWorks\PivotTests;

use EngineWorks\Pivot\Field;
use EngineWorks\Pivot\Pivot;
use PHPUnit\Framework\TestCase;

class PivotTest extends TestCase
{
    private function createPivot(string $source): Pivot
    {
        return new Pivot($source);
    }

    public function testConstructor(): void
    {
        $pivot = new Pivot();
        $this->assertSame('', $pivot->getSource());
        $this->assertSame('', $pivot->getInfo('author'));
        $this->assertNotEmpty($pivot->getInfo('created'));
        $this->assertSame('', $pivot->getInfo('description'));
        $this->assertSame('no', $pivot->getInfo('protected'));
        $this->assertSame([], $pivot->getSourceFields());
    }

    public function testConstructorWithSource(): void
    {
        $source = '...';
        $pivot = $this->createPivot($source);
        $this->assertSame($source, $pivot->getSource());
    }

    public function testGetInfoAndGetInfoArray(): void
    {
        $pivot = new Pivot();

        $this->assertSame('', $pivot->getInfo('author'));
        $this->assertFalse($pivot->getInfo('not-defined'));

        $info = $pivot->getInfoArray();
        $this->assertArrayHasKey('author', $info);
        $this->assertArrayHasKey('created', $info);
        $this->assertArrayHasKey('description', $info);
        $this->assertArrayHasKey('protected', $info);
    }

    public function testSourceFieldsAddGet(): void
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
