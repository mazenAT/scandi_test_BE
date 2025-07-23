<?php

namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        // Support Railway DATABASE_URL if present
        $databaseUrl = $_ENV['DB_URL'] ?? getenv('DB_URL');
        if ($databaseUrl) {
            $parts = parse_url($databaseUrl);
            $host = $parts['host'];
            $dbname = ltrim($parts['path'], '/');
            $username = $parts['user'];
            $password = $parts['pass'] ?? '';
            $port = $parts['port'] ?? 3306;
        } else {
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
            $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
            $username = $_ENV['DB_USER'] ?? getenv('DB_USER');
            $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
            $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306;
        }

        try {
            $this->connection = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}
