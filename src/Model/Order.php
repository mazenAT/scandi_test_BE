<?php

namespace App\Model;

use App\Database\Database;

abstract class Order
{
    protected $id;
    protected $createdAt;
    /** @var OrderItem[] */
    protected $items = [];

    abstract public function save(): int;
    abstract public static function fromArray(array $data): self;
    public function getId()
    {
        return $this->id;
    }
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    public function getItems()
    {
        return $this->items;
    }
}

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

abstract class OrderItem
{
    protected $productId;
    protected $quantity;
    /** @var OrderAttribute[] */
    protected $attributes = [];

    abstract public function save(int $orderId): void;
    abstract public static function fromArray(array $data): self;
    public function getProductId()
    {
        return $this->productId;
    }
    public function getQuantity()
    {
        return $this->quantity;
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
}

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

abstract class OrderAttribute
{
    protected $id;
    protected $value;
    abstract public static function fromArray(array $data): self;
    abstract public function toArray(): array;
    public function getId()
    {
        return $this->id;
    }
    public function getValue()
    {
        return $this->value;
    }
}

class SimpleOrderAttribute extends OrderAttribute
{
    public function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }
    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['value']);
    }
    public function toArray(): array
    {
        return ['id' => $this->id, 'value' => $this->value];
    }
}
