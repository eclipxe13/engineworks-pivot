<?php
namespace EngineWorks\PivotTests\Utils;

use EngineWorks\DBAL\Factory;
use EngineWorks\DBAL\Mssql\DBAL;

class DbConnection
{
    private $db;

    public function create()
    {
        $factory = new Factory('EngineWorks\DBAL\Mysqli');
        /** @var \EngineWorks\DBAL\Mysqli\Settings $settings */
        $settings = $factory->settings([
            'host' => getenv('MysqlHost'),
            'port' => getenv('MysqlPort'),
            'user' => getenv('MysqlUsername'),
            'password' => getenv('MysqlPassword'),
            'database' => getenv('MysqlDatabase'),
        ]);

        $this->db = $factory->dbal($settings);
        if (! $this->db->connect()) {
            throw new \RuntimeException('Cannot connect to database, are tests configured?');
        }
    }

    /** @return DBAL */
    public function getDb()
    {
        return $this->db;
    }

    /*
     * Singleton implementation
     */

    /** @var DbConnection */
    private static $singleton;

    /**
     * @return DbConnection
     */
    public static function singleton()
    {
        if (null === static::$singleton) {
            static::$singleton = new static();
            static::$singleton->create();
        }
        return static::$singleton;
    }

    public static function db()
    {
        return static::singleton()->getDb();
    }
}
