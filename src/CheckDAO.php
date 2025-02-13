<?php

namespace App;

use PDO;

class CheckDAO
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function save(Check $check): bool
    {
        $id = (int)$check->getUrlId();
        $statusCode = (int)$check->getStatusCode();
        $h1 = $check->getH1();
        $title = $check->getTitle();
        $description = $check->getDescription();
        $createdAt = $check->getCreatedAt();

        $sql = <<<SQL
        INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
        VALUES (:id, :statusCode, :h1, :title, :description, :createdAt);
        SQL;
        $result = $this->conn
            ->prepare($sql)
            ->execute(
                [
                    'id' => $id,
                    'statusCode' => $statusCode,
                    'h1' => $h1,
                    'title' => $title,
                    'description' => $description,
                    'createdAt' => $createdAt
                ]
            );
        $id = (string)$this->conn->lastInsertId();
        $check->setId($id);
        return $result;
    }

    public function findChecksBySiteId(string $id)
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $col = collect($stmt->fetchAll(PDO::FETCH_ASSOC));
        $result = $col->map(function (array $checkRow, int $key) {
            return Check::fromFetchArray($checkRow);
        })->All();
        return $result;
    }

    public function getLastChekStatusCode(string $siteUrl): string
    {
        $sql =  <<<SQL
                WITH checks as (
                SELECT c.status_code, c .created_at
                FROM public.urls as u INNER JOIN public.url_checks as c
                ON u.id = c.url_id
                WHERE u.name = ? 
                )
                SELECT status_code FROM checks WHERE 
                checks.created_at = (SELECT MAX(checks.created_at) FROM checks)
                SQL;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$siteUrl]);
        $row = $stmt->fetch();
        if ($row === false) {
            return '';
        }
        return (string)$row['status_code'];
    }
}
