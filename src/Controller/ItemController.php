<?php

namespace App\Controller;

use App\Model\Item;

class ItemController
{
    private $itemModel;

    public function __construct()
    {
        $this->itemModel = new Item();
    }

    public function getAll(): string
    {
        try {
            $items = $this->itemModel->getAll();
            return $this->jsonResponse(['success' => true, 'data' => $items]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getById(array $vars): string
    {
        try {
            $id = (int) $vars['id'];
            $item = $this->itemModel->getById($id);
            
            if (!$item) {
                return $this->jsonResponse(['success' => false, 'error' => 'Item not found'], 404);
            }
            
            return $this->jsonResponse(['success' => true, 'data' => $item]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function create(): string
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['title'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Title is required'], 400);
            }

            $id = $this->itemModel->create($input);
            $item = $this->itemModel->getById($id);
            
            return $this->jsonResponse(['success' => true, 'data' => $item], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function update(array $vars): string
    {
        try {
            $id = (int) $vars['id'];
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['title'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Title is required'], 400);
            }

            $success = $this->itemModel->update($id, $input);
            
            if (!$success) {
                return $this->jsonResponse(['success' => false, 'error' => 'Item not found'], 404);
            }

            $item = $this->itemModel->getById($id);
            return $this->jsonResponse(['success' => true, 'data' => $item]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function delete(array $vars): string
    {
        try {
            $id = (int) $vars['id'];
            $success = $this->itemModel->delete($id);
            
            if (!$success) {
                return $this->jsonResponse(['success' => false, 'error' => 'Item not found'], 404);
            }
            
            return $this->jsonResponse(['success' => true, 'message' => 'Item deleted successfully']);
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

            $items = $this->itemModel->search($query);
            return $this->jsonResponse(['success' => true, 'data' => $items]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getCategories(): string
    {
        try {
            $categories = $this->itemModel->getCategories();
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