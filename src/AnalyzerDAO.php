<?php

namespace App;

use \PDO;



class AnalyzerDAO{

    private PDO $conn;

    public function __construct(PDO $pdo) {
        $this->conn = $pdo;
        $this->init();
    }

    private function init() {
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
}





