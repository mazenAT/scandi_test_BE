<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Model\Category;
use App\Model\Product;

// Load JSON data
$jsonPath = __DIR__ . '/data.json';
if (!file_exists($jsonPath)) {
    exit("data.json not found in database directory.\n");
}
$json = file_get_contents($jsonPath);
$data = json_decode($json, true);
if (!$data) {
    exit("Invalid JSON in data.json.\n");
}

$db = Database::getInstance()->getConnection();
$categoryModel = new Category();

// Seed categories
$categoryMap = [];
foreach (
    $data['data']['categories'] as $cat
) {
    if ($cat['name'] === 'all') continue; // skip 'all'
    $db->prepare("INSERT IGNORE INTO categories (name) VALUES (?)")->execute([$cat['name']]);
    $catRow = $categoryModel->getByName($cat['name']);
    $categoryMap[$cat['name']] = $catRow['id'];
}

// Seed products
foreach (
    $data['data']['products'] as $prod
) {
    $category_id = $categoryMap[$prod['category']] ?? null;
    $db->prepare("INSERT IGNORE INTO products (id, name, in_stock, gallery, description, category_id, attributes, prices, brand) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            $prod['id'],
            $prod['name'],
            $prod['inStock'] ? 1 : 0,
            json_encode($prod['gallery']),
            $prod['description'],
            $category_id,
            json_encode($prod['attributes']),
            json_encode($prod['prices']),
            $prod['brand']
        ]);
}

echo "Seeding complete!\n"; 