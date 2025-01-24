<?php

namespace App;

use Exception;

class DbConnector
{
    private \PDO $conn;

    public function __construct(string $dbUrl)
    {
        $this->conn = $this->connect($dbUrl);
        $this->initTables();
    }

    private function connect(string $dbUrl): \PDO
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
            /*  if ($pdo === false) {
                throw new Exception('Failed to connect to database');
            }*/
            return $pdo;
        }
        throw new Exception('Failed to parse db URL');
    }

    private function getDbInitScript(): string
    {
        $sqlSiteTableCreate = file_get_contents(__DIR__ . '/../database.sql');
        if ($sqlSiteTableCreate === false) {
            throw new Exception('Failed to read Database initial script');
        }
        return (string)$sqlSiteTableCreate;
    }

    private function initTables()
    {
        $sql = $this->getDbInitScript();
        $result = $this->conn->exec($sql);
        if ($result === false) {
            throw new Exception('Failed to create database tables');
        }
    }


    public function getConnection(): \PDO
    {
        return $this->conn;
    }
}
