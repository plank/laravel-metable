<?php

namespace Plank\Metable\Tests\Integration\DataType;

use Plank\Metable\DataType\SerializableHandler;
use Plank\Metable\Tests\Mocks\SampleSerializable;
use Plank\Metable\Tests\TestCase;

class SerializableHandlerTest extends TestCase
{
    public function test_it_configures_allowed_classes(): void
    {
        $original = new SampleSerializable(['foo' => 'bar']);

        $handler = new SerializableHandler();

        $serialized = $handler->serializeValue($original);

        $incomplete = unserialize(serialize($original), ['allowed_classes' => false]);

        config()->set(
            'metable.serializableHandlerAllowedClasses',
            [SampleSerializable::class]
        );
        $this->assertEquals($original, $handler->unserializeValue($serialized));

        config()->set(
            'metable.serializableHandlerAllowedClasses',
            true
        );
        $this->assertEquals($original, $handler->unserializeValue($serialized));

        config()->set(
            'metable.serializableHandlerAllowedClasses',
            []
        );
        $this->assertEquals($incomplete, $handler->unserializeValue($serialized));

        config()->set(
            'metable.serializableHandlerAllowedClasses',
            false
        );
        $this->assertEquals($incomplete, $handler->unserializeValue($serialized));
    }
}
