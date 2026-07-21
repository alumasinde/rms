<?php

declare(strict_types=1);

namespace App\Core\Database;

use App\Core\Config\Config;
use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO singleton. Raw PDO only — no ORM.
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(Config $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host    = $config->get('database.host');
        $port    = $config->get('database.port');
        $db      = $config->get('database.database');
        $user    = $config->get('database.username');
        $pass    = $config->get('database.password');
        $charset = $config->get('database.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

        try {
            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        return self::$connection;
    }
}
