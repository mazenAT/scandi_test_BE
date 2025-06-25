<?php

namespace App\Controller;

use App\Model\Category;

class CategoryController
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function getAll(): string
    {
        try {
            $categories = $this->categoryModel->getAll();
            return $this->jsonResponse(['success' => true, 'data' => $categories]);
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
