<?php

namespace Plank\Metable\Tests\Integration;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plank\Metable\Meta;
use Plank\Metable\Tests\TestCase;

class MetaTest extends TestCase
{
    public function test_it_can_get_and_set_value()
    {
        $meta = $this->makeMeta();

        $meta->value = 'foo';

        $this->assertEquals('foo', $meta->value);
        $this->assertEquals('string', $meta->type);
    }

    public function test_it_exposes_its_serialized_value()
    {
        $meta = $this->makeMeta();
        $meta->value = 123;

        $this->assertEquals('123', $meta->getRawValue());
    }

    public function test_it_caches_unserialized_value()
    {
        $meta = $this->makeMeta();
        $meta->value = 'foo';
        $this->assertEquals('foo', $meta->value);

        $meta->setRawAttributes(['value' => 'bar'], true);

        $this->assertEquals('foo', $meta->value);
        $this->assertEquals('bar', $meta->getRawValue());
    }

    public function test_it_clears_cache_on_set()
    {
        $meta = $this->makeMeta();
        $meta->value = 'foo';
        $this->assertEquals('foo', $meta->value);

        $meta->value = 'bar';

        $this->assertEquals('bar', $meta->value);
    }

    public function test_it_can_get_its_model_relation()
    {
        $meta = $this->makeMeta();

        $relation = $meta->metable();

        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertEquals('metable_type', $relation->getMorphType());
        $this->assertEquals('metable_id', $relation->getForeignKeyName());
    }

    private function makeMeta(array $attributes = []): Meta
    {
        return factory(Meta::class)->make($attributes);
    }
}
