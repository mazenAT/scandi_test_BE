<?php

namespace App\Model;

use App\Database\Database;

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getByName(string $name): ?array
    {
        $sql = "SELECT * FROM categories WHERE name = ?";
        $stmt = $this->db->query($sql, [$name]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(string $name): int
    {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $this->db->query($sql, [$name]);
        return (int) $this->db->lastInsertId();
    }
}
