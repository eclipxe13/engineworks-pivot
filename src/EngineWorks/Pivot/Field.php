<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

use EngineWorks\DBAL\CommonTypes;

class Field
{
    const TEXT = 'TEXT';
    const NUMBER = 'NUMBER';
    const INT = 'INT';
    const DATE = 'DATE';
    const DATETIME = 'DATETIME';
    const TIME = 'TIME';

    /** @var string */
    private $fieldname;
    /** @var string */
    private $caption;
    /** @var string */
    private $type;

    /**
     * Field constructor.
     * @param string $fieldname
     * @param string $caption
     * @param string $type
     */
    public function __construct(string $fieldname, string $caption, string $type)
    {
        if (! in_array($type, [self::TEXT, self::INT, self::NUMBER, self::DATETIME, self::DATE, self::TIME])) {
            throw new \InvalidArgumentException('Invalid value for property type');
        }
        $this->fieldname = $fieldname;
        $this->caption = $caption;
        $this->type = $type;
    }

    public function getFieldname() : string
    {
        return $this->fieldname;
    }

    public function getCaption() : string
    {
        return $this->caption;
    }

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function toDBAL() : string
    {
        return self::typeToDBAL($this->type);
    }

    /**
     * @return string[]
     */
    public function asArray() : array
    {
        return [
            'fieldname' => $this->fieldname,
            'caption' => $this->caption,
            'type' => $this->type,
        ];
    }

    /**
     * @param string $type
     * @return string
     */
    public static function typeToDBAL(string $type) : string
    {
        switch ($type) {
            case self::TEXT:
                return CommonTypes::TTEXT;
            case self::INT:
                return CommonTypes::TINT;
            case self::NUMBER:
                return CommonTypes::TNUMBER;
            case self::DATETIME:
                return CommonTypes::TDATETIME;
            case self::DATE:
                return CommonTypes::TDATE;
            case self::TIME:
                return CommonTypes::TTIME;
            default:
                return CommonTypes::TTEXT;
        }
    }
}
