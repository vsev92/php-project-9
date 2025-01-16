<?php

namespace App;

use \PDO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class AnalyzerDAO{

    private PDO $conn;

    public function __construct(string $aDatabaseUrl) {

       ///database connection
       $databaseUrl = parse_url($aDatabaseUrl);
       $username = $databaseUrl['user']; // janedoe
       $password = $databaseUrl['pass']; // mypassword
       $host = $databaseUrl['host']; // localhost
       $port = (string)$databaseUrl['port'] ?? '5432'; // 5432
       $dbName = ltrim($databaseUrl['path'], '/');
       $dsn = "pgsql:host={$host};port={$port};dbname={$dbName};";
       $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
       if (!$pdo) {
            throw new Exception('Failed to connect to database');
       }
        $this->conn = $pdo;
        $this->init();
    }

    private function init() 
    {
        

        
        
        $sqlSiteTableCreate = file_get_contents(__DIR__.'/scripts/init.sql');
       // var_dump($sqlSiteTableCreate);
        $this->conn->exec($sqlSiteTableCreate);


    }

    public function save(Site $site)
    {
        if (is_null($site->getId())) {

            $url = $site->getUrl();
            $timestamp = $site->getTimestamp();
            $sql = "INSERT INTO sites(url, createdAt) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $url = $site->getUrl();
            $timestamp= $site->getTimestamp();
            $stmt->bindParam(1, $url);
            $stmt->bindParam(2, $timestamp);
            $stmt->execute();
    
            $id = (int) $this->conn->lastInsertId();
            $site->setId($id);
        } else {
        // Здесь код обновления существующей записи
        }
    }

    public function getAll() :array
    {
       
            $sql = "SELECT * FROM sites";
            
           
            $stmt = $this->conn->query($sql);
            $col = collect($stmt->fetchAll());
            
            $result = $col->map(function (array $siteItem, int $key) {
                
          
               return Site::fromFetchArrayRow($siteItem);
            })->All();
             

           
            return $result;
    }
}





