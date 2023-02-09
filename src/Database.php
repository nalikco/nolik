<?php

namespace App;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $pdo;

    public function __construct(string $dsn)
    {
        try {
            $this->pdo = new PDO($dsn);
        } catch(PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getRows(string $query, array $params = []): array|null
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();

        return $rows;
    }

    public function execute(string $query, array $params): void
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $stmt->closeCursor();
    }
}