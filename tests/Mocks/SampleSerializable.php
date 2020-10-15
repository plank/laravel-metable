<?php

namespace Plank\Metable\Tests\Mocks;

use Serializable;

class SampleSerializable implements Serializable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }
}
