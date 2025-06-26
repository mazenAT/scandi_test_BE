<?php

namespace App\Model;

abstract class OrderAttribute
{
    protected $id;
    protected $value;
    abstract public static function fromArray(array $data): self;
    abstract public function toArray(): array;
    public function getId() { return $this->id; }
    public function getValue() { return $this->value; }
} 