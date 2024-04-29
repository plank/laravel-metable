<?php

namespace Plank\Metable\Tests\Mocks;

use Serializable;

class SampleSerializable implements Serializable
{
    public $data;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize(string $data): void
    {
        $this->data = unserialize($data);
    }

    public function __serialize(): array
    {
        return $this->data;
    }

    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }
}
