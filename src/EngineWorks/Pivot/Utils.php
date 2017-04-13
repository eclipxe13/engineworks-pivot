<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

class Utils
{
    public static function escapeXml(string $text) : string
    {
        return htmlspecialchars($text, ENT_XML1);
    }
}
