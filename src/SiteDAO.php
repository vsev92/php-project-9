<?php

namespace App;

use \PDO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class SiteDAO
{

    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function save(Site $site): bool
    {
        $url = $site->getUrl();
        $timestamp = $site->getTimestamp();
        $sql = "INSERT INTO urls(name, created_at) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $url = $site->getUrl();
        $timestamp = $site->getTimestamp();
        $stmt->bindParam(1, $url);
        $stmt->bindParam(2, $timestamp);
        $result = $stmt->execute();
        $id = (int) $this->conn->lastInsertId();
        $site->setId($id);
        return $result;
    }

    public function getAll(): array
    {


        $sql = file_get_contents(__DIR__ . '/scripts/selectAllSitesWithStatus.sql');

        $stmt = $this->conn->query($sql);
        $col = collect($stmt->fetchAll(PDO::FETCH_ASSOC));

        $result = $col->map(function (array $siteItem, int $key) {


            return Site::fromFetchArrayRow($siteItem);
        })->All();



        return $result;
    }


    public function findByName($url)
    {
        $sql = "SELECT * FROM urls WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$url]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched !== false) {
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
        if ($fetched !== false) {
            return  Site::fromFetchArrayRow($fetched);
        }
        return null;
    }
}
