<?php

namespace App;

use PDO;
use Exception;

class DbConnection
{
    public static function fromDbUrl(string $dbUrl): PDO
    {
        $databaseUrl = parse_url($dbUrl);
        if (is_array($databaseUrl)) {
            $username = array_key_exists('user', $databaseUrl) ? $databaseUrl['user'] : '';
            $password = array_key_exists('pass', $databaseUrl) ? $databaseUrl['pass'] : '';
            $host = array_key_exists('host', $databaseUrl) ? $databaseUrl['host'] : '';
            $port = array_key_exists('port', $databaseUrl) ? (string)$databaseUrl['port'] : '5432';
            $dbName = array_key_exists('path', $databaseUrl) ? ltrim($databaseUrl['path'], '/') : '';
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName};";
            $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            return $pdo;
        }
        throw new Exception('Failed to parse db URL');
    }
}
