<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

use EngineWorks\DBAL\DBAL;

class Filter
{
    const EQUAL = '=';
    const NEQUAL = '<>';
    const GRATER = '>';
    const GRATEREQUAL = '>=';
    const LESS = '<';
    const LESSEQUAL = '<=';
    const IN = 'IN';
    const CONTAINS = 'HAVE';

    /** @var string */
    private $fieldname;
    /** @var string */
    private $operator;
    /** @var mixed */
    private $arguments;

    public function __construct(string $fieldname, string $operator, $arguments)
    {
        $this->fieldname = $fieldname;

        if (! static::operatorExists($operator)) {
            throw new \InvalidArgumentException('Invalid operator');
        }
        $this->operator = $operator;

        if (is_null($arguments)) {
            throw new \InvalidArgumentException('Invalid argument, is null');
        } elseif (static::IN == $this->operator && ! is_array($arguments)) {
            throw new \InvalidArgumentException('Invalid argument, must be an array');
        } elseif (static::IN != $this->operator && is_array($arguments)) {
            throw new \InvalidArgumentException('Invalid argument, cannot be an array');
        }
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getFieldname(): string
    {
        return $this->fieldname;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get a string condition to use in a sql where clause
     *
     * @param DBAL $db
     * @param string $dbalType
     * @return string
     */
    public function getSQL(DBAL $db, string $dbalType) : string
    {
        if ($this->operator == self::IN) {
            return $db->sqlFieldEscape($this->fieldname) . ' IN ' . $db->sqlQuoteIn($this->arguments, $dbalType);
        }
        if ($this->operator == self::CONTAINS) {
            return $db->sqlLike($db->sqlFieldEscape($this->fieldname), $this->arguments);
        }
        return $db->sqlFieldEscape($this->fieldname)
            . ' ' . $this->operator . ' '
            . $db->sqlQuote($this->arguments, $dbalType);
    }

    /**
     * @return array
     */
    public function asArray() : array
    {
        return [
            'fieldname' => $this->fieldname,
            'operator' => $this->operator,
            'arguments' => $this->arguments,
        ];
    }

    public static function operators() : array
    {
        return [
            static::EQUAL,
            static::NEQUAL,
            static::GRATER,
            static::GRATEREQUAL,
            static::LESS,
            static::LESSEQUAL,
            static::IN,
            static::CONTAINS,
        ];
    }

    public static function operatorExists(string $operator) : bool
    {
        return in_array($operator, static::operators());
    }
}
