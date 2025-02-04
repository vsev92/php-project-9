<?php

namespace App;

use Exception;

class DbProvider
{
    public static function fromDbUrl(string $dbUrl): \PDO
    {
        $databaseUrl = parse_url($dbUrl);
        if (is_array($databaseUrl)) {
            $username = array_key_exists('user', $databaseUrl) ? $databaseUrl['user'] : '';
            $password = array_key_exists('pass', $databaseUrl) ? $databaseUrl['pass'] : '';
            $host = array_key_exists('host', $databaseUrl) ? $databaseUrl['host'] : '';
            $port = array_key_exists('port', $databaseUrl) ? (string)$databaseUrl['port'] : '5432';
            $dbName = array_key_exists('path', $databaseUrl) ? ltrim($databaseUrl['path'], '/') : '';
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName};";
            $pdo = new \PDO($dsn, $username, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            return $pdo;
        }
        throw new Exception('Failed to parse db URL');
    }

    private static function getMigrateScript(): string
    {
        $sqMigrateScript = file_get_contents(__DIR__ . '/../database.sql');
        if ($sqMigrateScript === false) {
            throw new Exception('Failed to read Database initial script');
        }
        return $sqMigrateScript;
    }

    public static function migrate(\PDO $conn)
    {
        $sql = self::getMigrateScript();
        $result = $conn->exec($sql);
        if ($result === false) {
            throw new Exception('Failed to migrate tables');
        }
    }
}
