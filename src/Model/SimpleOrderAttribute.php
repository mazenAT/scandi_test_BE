<?php

namespace App\Model;

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