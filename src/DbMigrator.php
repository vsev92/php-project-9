<?php

namespace App;

use PDO;
use Exception;

class DbMigrator
{
    private static function getMigrateScript(): string
    {
        $sqMigrateScript = file_get_contents(__DIR__ . '/../database.sql');
        if ($sqMigrateScript === false) {
            throw new Exception('Failed to read Database initial script');
        }
        return $sqMigrateScript;
    }

    public static function migrate(PDO $conn)
    {
        $sql = self::getMigrateScript();
        $result = $conn->exec($sql);
        if ($result === false) {
            throw new Exception('Failed to migrate tables');
        }
    }
}
