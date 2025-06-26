<?php

namespace App\Model;

use App\Database\Database;

class SimpleOrderItem extends OrderItem
{
    public function __construct($productId, $quantity, $attributes = [])
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->attributes = $attributes;
    }

    public function save(int $orderId): void
    {
        $db = Database::getInstance()->getConnection();
        $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, attributes) VALUES (?, ?, ?, ?)")
            ->execute([
                $orderId,
                $this->productId,
                $this->quantity,
                json_encode(array_map(fn($a) => $a->toArray(), $this->attributes))
            ]);
    }

    public static function fromArray(array $data): self
    {
        $attributes = [];
        if (!empty($data['attributes'])) {
            foreach ($data['attributes'] as $attr) {
                $attributes[] = SimpleOrderAttribute::fromArray($attr);
            }
        }
        return new self($data['productId'], $data['quantity'], $attributes);
    }
} 