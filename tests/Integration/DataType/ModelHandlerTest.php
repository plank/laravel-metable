<?php

namespace Plank\Metable\Tests\Integration\DataType;

use Plank\Metable\DataType\ModelHandler;
use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\TestCase;

class ModelHandlerTest extends TestCase
{
    public function test_it_reloads_a_model_instance()
    {
        $this->useDatabase();

        $model = factory(SampleMetable::class)->create(['id' => 12]);
        $handler = new ModelHandler();

        $serialized = $handler->serializeValue($model);
        $unserialized = $handler->unserializeValue($serialized);

        $this->assertInstanceOf(SampleMetable::class, $unserialized);
        $this->assertEquals(12, $unserialized->getKey());
        $this->assertTrue($unserialized->exists);
    }
}
