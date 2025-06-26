<?php

namespace App\Model;

abstract class OrderItem
{
    protected $productId;
    protected $quantity;
    /** @var OrderAttribute[] */
    protected $attributes = [];

    abstract public function save(int $orderId): void;
    abstract public static function fromArray(array $data): self;
    public function getProductId() { return $this->productId; }
    public function getQuantity() { return $this->quantity; }
    public function getAttributes() { return $this->attributes; }
} 