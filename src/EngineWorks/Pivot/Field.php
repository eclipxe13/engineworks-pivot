<?php

declare(strict_types=1);

namespace EngineWorks\Pivot;

use EngineWorks\DBAL\CommonTypes;
use InvalidArgumentException;

class Field
{
    public const TEXT = 'TEXT';

    public const NUMBER = 'NUMBER';

    public const INT = 'INT';

    public const DATE = 'DATE';

    public const DATETIME = 'DATETIME';

    public const TIME = 'TIME';

    private readonly string $fieldName;

    private readonly string $caption;

    private readonly string $type;

    public function __construct(string $fieldName, string $caption, string $type)
    {
        if (! in_array($type, self::types(), true)) {
            throw new InvalidArgumentException('Invalid value for property type.');
        }
        $this->fieldName = $fieldName;
        $this->caption = $caption;
        $this->type = $type;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toDBAL(): string
    {
        return self::typeToDBAL($this->type);
    }

    /**
     * @return array{fieldname: string, caption: string, type: string}
     */
    public function asArray(): array
    {
        return [
            'fieldname' => $this->fieldName,
            'caption' => $this->caption,
            'type' => $this->type,
        ];
    }

    public static function typeToDBAL(string $type): string
    {
        return match ($type) {
            self::INT => CommonTypes::TINT,
            self::NUMBER => CommonTypes::TNUMBER,
            self::DATETIME => CommonTypes::TDATETIME,
            self::DATE => CommonTypes::TDATE,
            self::TIME => CommonTypes::TTIME,
            default => CommonTypes::TTEXT,
        };
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TEXT,
            self::INT,
            self::NUMBER,
            self::DATETIME,
            self::DATE,
            self::TIME,
        ];
    }
}
