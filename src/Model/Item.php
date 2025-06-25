<?php

namespace App\Model;

use App\Database\Database;

class Item
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM items ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM items WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO items (title, description, price, category, status) VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['title'],
            $data['description'] ?? '',
            $data['price'] ?? 0.00,
            $data['category'] ?? '',
            $data['status'] ?? 'active'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE items SET title = ?, description = ?, price = ?, category = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->query($sql, [
            $data['title'],
            $data['description'] ?? '',
            $data['price'] ?? 0.00,
            $data['category'] ?? '',
            $data['status'] ?? 'active',
            $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM items WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function search(string $query): array
    {
        $sql = "SELECT * FROM items WHERE title LIKE ? OR description LIKE ? OR category LIKE ? ORDER BY created_at DESC";
        $searchTerm = "%$query%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function getByCategory(string $category): array
    {
        $sql = "SELECT * FROM items WHERE category = ? ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$category]);
        return $stmt->fetchAll();
    }

    public function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM items WHERE category IS NOT NULL AND category != '' ORDER BY category";
        $stmt = $this->db->query($sql);
        return array_column($stmt->fetchAll(), 'category');
    }
} 