<?php

namespace App\Model;

use App\Database\Database;

class SimpleOrder extends Order
{
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function save(): int
    {
        $db = Database::getInstance()->getConnection();
        $db->prepare("INSERT INTO orders () VALUES ()")->execute();
        $this->id = $db->lastInsertId();
        foreach ($this->items as $item) {
            $item->save($this->id);
        }
        return $this->id;
    }

    public static function fromArray(array $data): self
    {
        $items = [];
        foreach ($data['products'] as $itemData) {
            $items[] = SimpleOrderItem::fromArray($itemData);
        }
        return new self($items);
    }
} 