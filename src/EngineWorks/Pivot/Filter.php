<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

use EngineWorks\DBAL\DBAL;

class Filter
{
    public const EQUAL = '=';
    public const NEQUAL = '<>';
    public const GREATER = '>';
    public const GREATEREQUAL = '>=';
    public const LESS = '<';
    public const LESSEQUAL = '<=';
    public const IN = 'IN';
    public const CONTAINS = 'HAVE';

    private readonly string $fieldName;
    private readonly string $operator;
    private readonly mixed $arguments;

    public function __construct(string $fieldName, string $operator, mixed $arguments)
    {
        $this->fieldName = $fieldName;

        if (! self::operatorExists($operator)) {
            throw new \InvalidArgumentException('Invalid operator.');
        }
        $this->operator = $operator;

        if (null === $arguments) {
            throw new \InvalidArgumentException('Invalid argument, is null.');
        }
        if (self::IN === $operator && ! is_array($arguments)) {
            throw new \InvalidArgumentException('Invalid argument, must be an array.');
        }
        if (self::IN !== $operator && is_array($arguments)) {
            throw new \InvalidArgumentException('Invalid argument, cannot be an array.');
        }
        $this->arguments = $arguments;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getArguments(): mixed
    {
        return $this->arguments;
    }

    /**
     * Get a string condition to use in a sql where clause
     */
    public function getSQL(DBAL $db, string $dbalType): string
    {
        if (self::IN === $this->operator) {
            if (! is_array($this->arguments)) {
                throw new \LogicException('The IN operator requires an array of arguments.');
            }
            return $db->sqlFieldEscape($this->fieldName) . ' IN ' . $db->sqlQuoteIn($this->arguments, $dbalType);
        }
        if (self::CONTAINS === $this->operator) {
            if (! is_scalar($this->arguments)) {
                throw new \LogicException('The HAVE operator requires a scalar argument.');
            }
            return $db->sqlLike($db->sqlFieldEscape($this->fieldName), (string) $this->arguments);
        }
        return $db->sqlFieldEscape($this->fieldName)
            . ' ' . $this->operator . ' '
            . $db->sqlQuote($this->arguments, $dbalType);
    }

    /**
     * @return array{fieldname: string, operator: string, arguments: mixed}
     */
    public function asArray(): array
    {
        return [
            'fieldname' => $this->fieldName,
            'operator' => $this->operator,
            'arguments' => $this->arguments,
        ];
    }

    /**
     * @return list<string>
     */
    public static function operators(): array
    {
        return [
            self::EQUAL,
            self::NEQUAL,
            self::GREATER,
            self::GREATEREQUAL,
            self::LESS,
            self::LESSEQUAL,
            self::IN,
            self::CONTAINS,
        ];
    }

    public static function operatorExists(string $operator): bool
    {
        return in_array($operator, self::operators(), true);
    }
}
