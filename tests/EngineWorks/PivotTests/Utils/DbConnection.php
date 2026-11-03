<?php

declare(strict_types=1);
namespace EngineWorks\PivotTests\Utils;

use EngineWorks\DBAL\DBAL;
use EngineWorks\DBAL\Factory;
use EngineWorks\DBAL\Mysqli\Settings;
use mysqli_driver;
use RuntimeException;

final class DbConnection
{
    private DBAL $db;

    /** @noinspection PhpObjectFieldsAreOnlyWrittenInspection */
    public function create(): void
    {
        $mysqliDriver = new mysqli_driver();
        $mysqliDriver->report_mode = MYSQLI_REPORT_OFF;

        $factory = new Factory('EngineWorks\DBAL\Mysqli');
        /** @var Settings $settings */
        $settings = $factory->settings([
            'host' => $this->getEnv('TEST_MYSQL_HOST'),
            'port' => $this->getEnv('TEST_MYSQL_PORT'),
            'user' => $this->getEnv('TEST_MYSQL_USERNAME'),
            'password' => $this->getEnv('TEST_MYSQL_PASSWORD'),
            'database' => $this->getEnv('TEST_MYSQL_DATABASE'),
        ]);

        $this->db = $factory->dbal($settings);
        if (! $this->db->connect()) {
            throw new RuntimeException('Cannot connect to database, are tests configured?');
        }
    }

    public function getDb(): DBAL
    {
        return $this->db;
    }

    /*
     * Singleton implementation
     */

    private static DbConnection|null $singleton = null;

    /**
     * @return DbConnection
     */
    public static function singleton(): self
    {
        if (null === self::$singleton) {
            self::$singleton = new self();
            self::$singleton->create();
        }
        return self::$singleton;
    }

    public static function db(): DBAL
    {
        return self::singleton()->getDb();
    }

    private function getEnv(string $key): string
    {
        return isset($_SERVER[$key]) && is_scalar($_SERVER[$key]) ? (string) $_SERVER[$key] : '';
    }
}
