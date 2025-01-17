<?php

namespace App;

use \PDO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class CheckDAO{

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
    
        $sqlSiteTablesCreate = file_get_contents(__DIR__.'/scripts/database.sql');
        $this->conn->exec($sqlSiteTablesCreate);


    }

    public function save(Check $check) :bool
    {

            $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);

            $id = (int)$check->getUrlId();
            $statusCode = (int)$check->getStatusCode();
            $h1 = $check->getH1();
            $title = $check->getTitle();
            $description= $check->getDescription();
            $createdAt= $check->getCreatedAt();

            $stmt->bindParam(1, $id);
            $stmt->bindParam(2, $statusCode);
            $stmt->bindParam(3, $h1);
            $stmt->bindParam(4, $title);
            $stmt->bindParam(5, $description);
            $stmt->bindParam(6, $createdAt);

            $result = $stmt->execute();
            $id = (int) $this->conn->lastInsertId();
            $check->setId($id);
            return $result;
            
    }

    public function findChecksBySiteId(string $id)
    {
            $sql = "SELECT * FROM url_checks WHERE url_id = ?"; 
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);   
            
            $col = collect($stmt->fetchAll(PDO::FETCH_ASSOC));
            $result = $col->map(function (array $checkRow, int $key) {
               return Check::fromFetchArray($checkRow);
            })->All(); 
            return $result;
    }


}





