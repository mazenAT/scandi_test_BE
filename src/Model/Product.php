<?php

namespace App\Model;

use App\Database\Database;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql);
        $products = $stmt->fetchAll();
        return array_map([$this, 'mapProduct'], $products);
    }

    public function getById(string $id): ?array
    {
        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $product = $stmt->fetch();
        return $product ? $this->mapProduct($product) : null;
    }

    public function create(array $data): string
    {
        $sql = "INSERT INTO products (id, name, in_stock, gallery, description, category_id, attributes, prices, brand) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['id'],
            $data['name'],
            $data['inStock'] ?? true,
            json_encode($data['gallery'] ?? []),
            $data['description'] ?? '',
            $data['category_id'] ?? null,
            json_encode($data['attributes'] ?? []),
            json_encode($data['prices'] ?? []),
            $data['brand'] ?? ''
        ]);
        return $data['id'];
    }

    public function update(string $id, array $data): bool
    {
        $sql = "UPDATE products SET name = ?, in_stock = ?, gallery = ?, description = ?, category_id = ?, attributes = ?, prices = ?, brand = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->query($sql, [
            $data['name'],
            $data['inStock'] ?? true,
            json_encode($data['gallery'] ?? []),
            $data['description'] ?? '',
            $data['category_id'] ?? null,
            json_encode($data['attributes'] ?? []),
            json_encode($data['prices'] ?? []),
            $data['brand'] ?? '',
            $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function getByCategory(string $categoryName): array
    {
        if (strtolower($categoryName) === 'all') {
            return $this->getAll();
        }
        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE c.name = ? ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql, [$categoryName]);
        $products = $stmt->fetchAll();
        return array_map([$this, 'mapProduct'], $products);
    }

    public function search(string $query): array
    {
        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ? ORDER BY p.created_at DESC";
        $searchTerm = "%$query%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
        $products = $stmt->fetchAll();
        return array_map([$this, 'mapProduct'], $products);
    }

    private function mapProduct(array $row): array
    {
        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'inStock' => (bool)$row['in_stock'],
            'gallery' => json_decode($row['gallery'], true) ?? [],
            'description' => $row['description'],
            'category' => $row['category_name'],
            'attributes' => json_decode($row['attributes'], true) ?? [],
            'prices' => json_decode($row['prices'], true) ?? [],
            'brand' => $row['brand'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }
}
