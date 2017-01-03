<?php

use Plank\Metable\Meta;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MetaTest extends TestCase
{
	public function test_it_can_get_and_set_value()
	{
		$meta = factory(Meta::class)->make();

		$meta->value = 'foo';

		$this->assertEquals('foo', $meta->value);
		$this->assertEquals('string', $meta->type);
	}

	public function test_it_exposes_its_serialized_value()
	{
		$meta = factory(Meta::class)->make();
		$meta->value = 123;

		$this->assertEquals('123', $meta->getRawValue());
	}
	
	public function test_it_caches_unserialized_value()
	{
		$meta = factory(Meta::class)->make();
		$meta->value = 'foo';
		$this->assertEquals('foo', $meta->value);

		$meta->setRawAttributes(['value' => 'bar'], true);

		$this->assertEquals('foo', $meta->value);
		$this->assertEquals('bar', $meta->getRawValue());
	}

	public function test_it_clears_cache_on_set()
	{
		$meta = factory(Meta::class)->make();
		$meta->value = 'foo';
		$this->assertEquals('foo', $meta->value);

		$meta->value = 'bar';

		$this->assertEquals('bar', $meta->value);
	}

	public function test_it_can_get_its_model_relation()
	{
		$meta = factory(Meta::class)->make();

		$relation = $meta->metable();

		$this->assertInstanceOf(MorphTo::class, $relation);
		$this->assertEquals('metable_type', $relation->getMorphType());
		$this->assertEquals('metable_id', $relation->getForeignKey());
	}
}