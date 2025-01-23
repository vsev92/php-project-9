<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CheckDAO
{
    private \PDO $conn;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    public function save(Check $check): bool
    {

        $sql = <<<SQL
        INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
        SQL;
        $stmt = $this->conn->prepare($sql);

        $id = (int)$check->getUrlId();
        $statusCode = (int)$check->getStatusCode();
        $h1 = $check->getH1();
        $title = $check->getTitle();
        $description = $check->getDescription();
        $createdAt = $check->getCreatedAt();

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

        $col = collect($stmt->fetchAll(\PDO::FETCH_ASSOC));
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
        $stmt->execute($siteUrl);
        if ($result = $stmt->fetch() !== false) {
            return (string)$result['status_code'];
        }
        return '';
    }
}
