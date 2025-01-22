<?php

namespace App;

use \PDO;



class DBConnector{

    private PDO $conn;

    public function __construct() {



        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
        $aDatabaseUrl = (string)$_ENV['DATABASE_URL'];
       ///database connection
       dump($aDatabaseUrl);
       $databaseUrl = parse_url($aDatabaseUrl);
       $username = $databaseUrl['user']; // janedoe
       $password = $databaseUrl['pass']; // mypassword
       $host = $databaseUrl['host']; // localhost
       $port = (string)$databaseUrl['port'] ?? '5432'; // 5432
       $dbName = ltrim($databaseUrl['path'], '/');
       $dsn = "pgsql:host={$host};port={$port};dbname={$dbName};";
       dump($dsn);
       $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
       if (!$pdo) {
            throw new Exception('Failed to connect to database');
       }
        $this->conn = $pdo;
        $this->init();
    }

    private function init() 
    {
        

        
        
        $sqlSiteTableCreate = file_get_contents(__DIR__.'/../database.sql');
        $this->conn->exec($sqlSiteTableCreate);


    }

    
    public function getConnection() :PDO
    {
       return $this->conn;
    }





}





