<?php

namespace App\Controller;

use App\Model\Product;

class ProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function getAll(): string
    {
        try {
            $products = $this->productModel->getAll();
            return $this->jsonResponse(['success' => true, 'data' => $products]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getById(array $vars): string
    {
        try {
            $id = $vars['id'];
            $product = $this->productModel->getById($id);
            if (!$product) {
                return $this->jsonResponse(['success' => false, 'error' => 'Product not found'], 404);
            }
            return $this->jsonResponse(['success' => true, 'data' => $product]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function search(): string
    {
        try {
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Search query is required'], 400);
            }
            $products = $this->productModel->search($query);
            return $this->jsonResponse(['success' => true, 'data' => $products]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($data);
    }
} 