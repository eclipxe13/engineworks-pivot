<?php
declare(strict_types = 1);
namespace EngineWorks\Pivot;

use EngineWorks\DBAL\DBAL;

class Aggregate
{
    const COUNT = 'COUNT';
    const SUM = 'SUM';
    const AVG = 'AVG';
    const MIN = 'MIN';
    const MAX = 'MAX';

    const ORDERNONE = '';
    const ORDERASC = 'ASC';
    const ORDERDESC = 'DESC';

    private $fieldname;
    private $asname;
    private $caption;
    private $group;
    private $decimals;
    private $order;

    public function __construct(
        string $fieldname,
        string $asname,
        string $caption,
        string $group,
        int $decimals,
        string $order
    ) {
        if (! $this->groupExists($group)) {
            throw new \InvalidArgumentException('Invalid value for group');
        }
        if ($decimals < 0 || $decimals > 8) {
            throw new \InvalidArgumentException('Invalid value for decimals');
        }
        if (! in_array($order, [static::ORDERNONE, static::ORDERASC, static::ORDERDESC])) {
            throw new \InvalidArgumentException('Invalid order value');
        }
        $this->fieldname = $fieldname;
        $this->asname = $asname;
        $this->caption = $caption;
        $this->group = $group;
        $this->decimals = $decimals;
        $this->order = $order;
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
    public function getAsname(): string
    {
        return $this->asname;
    }

    /**
     * @return string
     */
    public function getCaption(): string
    {
        return $this->caption;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getDecimals(): int
    {
        return $this->decimals;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    public function asArray() : array
    {
        return [
            'fieldname' => $this->fieldname,
            'caption' => $this->caption,
            'group' => $this->group,
            'asname' => $this->asname,
            'decimals' => $this->decimals,
            'order' => $this->order,
        ];
    }

    /**
     * @param DBAL $db
     * @return string
     */
    public function getSQL(DBAL $db) : string
    {
        return $this->group
            . '(' . $db->sqlFieldEscape($this->fieldname) . ')'
            . ' AS ' . $db->sqlFieldEscape($this->asname);
    }

    public static function groupTypes() : array
    {
        return [
            static::COUNT,
            static::SUM,
            static::AVG,
            static::MIN,
            static::MAX,
        ];
    }

    public static function groupExists(string $group) : bool
    {
        return in_array($group, static::groupTypes());
    }
}
