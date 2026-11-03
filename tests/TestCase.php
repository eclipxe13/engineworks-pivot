<?php

declare(strict_types=1);

namespace EngineWorks\Pivot\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function assetPath(string $path): string
    {
        return __DIR__ . '/_files/' . $path;
    }
}
