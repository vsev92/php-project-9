<?php

namespace App;

class DbConnector
{
    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
        $dbUrl = (string)$_ENV['DATABASE_URL'];
        $this->conn = $this->connect($dbUrl);
        $this->init();
    }

    private function connect($dbUrl)
    {
        $databaseUrl = parse_url($dbUrl);
        $username = $databaseUrl['user'];
        $password = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $port = array_key_exists('port', $databaseUrl) ? (string)$databaseUrl['port'] : '5432';
        $dbName = ltrim($databaseUrl['path'], '/');
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbName};";
        $pdo = new \PDO($dsn, $username, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        if (!$pdo) {
            throw new Exception('Failed to connect to database');
        }
        return $pdo;
    }

    private function init()
    {

        $sqlSiteTableCreate = file_get_contents(__DIR__ . '/../database.sql');
        $this->conn->exec($sqlSiteTableCreate);
    }


    public function getConnection(): \PDO
    {
        return $this->conn;
    }
}
