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
        

        
        
        $sqlSiteTableCreate = file_get_contents(__DIR__.'/scripts/database.sql');
       // var_dump($sqlSiteTableCreate);
        $this->conn->exec($sqlSiteTableCreate);


    }

    public function save(Site $site) :bool
    {
            $url = $site->getUrl();
            $timestamp = $site->getTimestamp();
            $sql = "INSERT INTO urls(name, created_at) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $url = $site->getUrl();
            $timestamp= $site->getTimestamp();
            $stmt->bindParam(1, $url);
            $stmt->bindParam(2, $timestamp);
            $result = $stmt->execute();
            $id = (int) $this->conn->lastInsertId();
            $site->setId($id);
            return $result;
            
    }

    public function getAll() :array
    {
       
            $sql = "SELECT * FROM urls";
            
           
            $stmt = $this->conn->query($sql);
            $col = collect($stmt->fetchAll(PDO::FETCH_ASSOC));
            
            $result = $col->map(function (array $siteItem, int $key) {
                
          
               return Site::fromFetchArrayRow($siteItem);
            })->All();
             

           
            return $result;
    }


    public function findByName($name)
    {
            $sql = "SELECT * FROM urls WHERE name = ?"; 
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$name]);   
            $fetched = $stmt->fetch(PDO::FETCH_ASSOC)[0];
            if(!is_null($fetched)) {
                return  Site::fromFetchArrayRow($fetched);
            }
            return null;    
    }

    public function findById(string $id)
    {
            $sql = "SELECT * FROM urls WHERE id = ?"; 
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);   
            $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!is_null($fetched)) {
                return  Site::fromFetchArrayRow($fetched);
            }
            return null;    
    }


}





