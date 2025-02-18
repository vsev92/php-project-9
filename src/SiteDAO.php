<?php

namespace App;

use PDO;

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
        $sql = "INSERT INTO urls(name, created_at) VALUES (:name, :createdAt)";
        $result = $this->conn
            ->prepare($sql)
            ->execute(
                [
                    'name' => $url,
                    'createdAt' => $timestamp,
                ]
            );
        $id = (string)$this->conn->lastInsertId();
        $site->setId($id);
        return $result;
    }



    public function getAll(): array
    {
        $sql = <<<SQL
        SELECT u.id, u.name, u.created_at, lc.status_code
        FROM urls as u INNER JOIN last_checks as lc 
        ON u.id = lc.url_id
        SQL;

        $stmt = $this->conn->query($sql);
        $col = collect($stmt->fetchAll(PDO::FETCH_ASSOC));
        $result = $col->map(function (array $siteItem, int $key) {
            return Site::fromFetchArrayRow($siteItem);
        })->All();

        return $result;
    }

    public function findByName(string $name): ?Site
    {
        $sql = "SELECT * FROM urls WHERE name = :name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['name' => $name]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched !== false) {
            return  Site::fromFetchArrayRow($fetched);
        }
        return null;
    }

    public function findById(string $id): ?Site
    {
        $sql = "SELECT * FROM urls WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched !== false) {
            return  Site::fromFetchArrayRow($fetched);
        }
        return null;
    }
}
