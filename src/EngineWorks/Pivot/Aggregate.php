<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

use EngineWorks\DBAL\DBAL;

class Aggregate
{
    public const COUNT = 'COUNT';
    public const SUM = 'SUM';
    public const AVG = 'AVG';
    public const MIN = 'MIN';
    public const MAX = 'MAX';

    public const ORDERNONE = '';
    public const ORDERASC = 'ASC';
    public const ORDERDESC = 'DESC';

    private readonly string $fieldName;
    private readonly string $asName;
    private readonly string $caption;
    private readonly string $group;
    private readonly int $decimals;
    private readonly string $order;

    public function __construct(
        string $fieldName,
        string $asName,
        string $caption,
        string $group,
        int $decimals,
        string $order
    ) {
        if (! self::groupExists($group)) {
            throw new \InvalidArgumentException('Invalid value for group.');
        }
        if ($decimals < 0 || $decimals > 8) {
            throw new \InvalidArgumentException('Invalid value for decimals.');
        }
        if (! in_array($order, [self::ORDERNONE, self::ORDERASC, self::ORDERDESC], true)) {
            throw new \InvalidArgumentException('Invalid order value.');
        }
        $this->fieldName = $fieldName;
        $this->asName = $asName;
        $this->caption = $caption;
        $this->group = $group;
        $this->decimals = $decimals;
        $this->order = $order;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getAsName(): string
    {
        return $this->asName;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @return array{fieldname: string, caption: string, group: string, asname: string, decimals: int, order: string}
     */
    public function asArray(): array
    {
        return [
            'fieldname' => $this->fieldName,
            'caption' => $this->caption,
            'group' => $this->group,
            'asname' => $this->asName,
            'decimals' => $this->decimals,
            'order' => $this->order,
        ];
    }

    public function getSQL(DBAL $db): string
    {
        return $this->group
            . '(' . $db->sqlFieldEscape($this->fieldName) . ')'
            . ' AS ' . $db->sqlFieldEscape($this->asName);
    }

    /**
     * @return list<string>
     */
    public static function groupTypes(): array
    {
        return [
            self::COUNT,
            self::SUM,
            self::AVG,
            self::MIN,
            self::MAX,
        ];
    }

    public static function groupExists(string $group): bool
    {
        return in_array($group, self::groupTypes(), true);
    }
}
