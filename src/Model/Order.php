<?php

namespace App\Model;

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
