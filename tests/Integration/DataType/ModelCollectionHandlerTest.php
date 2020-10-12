<?php

namespace Plank\Metable\Tests\Integration\DataType;

use Illuminate\Database\Eloquent\Collection;
use Plank\Metable\DataType\ModelCollectionHandler;
use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\TestCase;

class ModelCollectionHandlerTest extends TestCase
{
    public function test_it_reloads_model_instances()
    {
        $this->useDatabase();

        $model1 = factory(SampleMetable::class)->create(['id' => 3]);
        $model2 = factory(SampleMetable::class)->make([]);
        $model3 = factory(SampleMetable::class)->create(['id' => 1]);
        $collection = new Collection([$model1, $model2, 'foo' => $model3]);
        $handler = new ModelCollectionHandler();

        $serialized = $handler->serializeValue($collection);
        /** @var Collection $unserialized */
        $unserialized = $handler->unserializeValue($serialized);

        $this->assertInstanceOf(Collection::class, $unserialized);
        $this->assertInstanceOf(SampleMetable::class, $unserialized[0]);
        $this->assertInstanceOf(SampleMetable::class, $unserialized[1]);
        $this->assertInstanceOf(SampleMetable::class, $unserialized['foo']);
        $this->assertEquals(3, $unserialized[0]->getKey());
        $this->assertFalse($unserialized[1]->exists);
        $this->assertEquals(1, $unserialized['foo']->getKey());
    }
}
