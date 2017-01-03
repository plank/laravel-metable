<?php

use Illuminate\Database\Eloquent\Relations\MorphMany;

class MetableTest extends TestCase
{
	public function test_it_can_load_its_meta_relation()
	{
		$metable = factory(SampleMetable::class)->make();

		$relation = $metable->meta();

		$this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertEquals('meta.metable_type', $relation->getMorphType());
        $this->assertEquals('meta.metable_id', $relation->getForeignKey());
        $this->assertEquals('sample_metables.id', $relation->getQualifiedParentKeyName());
	}

	public function test_it_can_get_and_set_meta_value_by_key()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$this->assertFalse($metable->hasMeta('foo'));

		$metable->setMeta('foo', 'bar');

		$this->assertTrue($metable->hasMeta('foo'));
		$this->assertEquals('bar', $metable->getMeta('foo'));
	}

	public function test_it_can_get_meta_record()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 123);

		$record = $metable->getMetaRecord('foo');

		$this->assertEquals('foo', $record->key);
		$this->assertEquals(123, $record->value);
	}

	public function test_it_updates_existing_meta_records()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 123);
		$record = $metable->getMetaRecord('foo');

		$metable->setMeta('foo', 321);
		$new_record = $metable->fresh(['meta'])->getMetaRecord('foo');

		$this->assertEquals($record->getKey(), $new_record->getKey());
		$this->assertEquals(321, $new_record->value);
	}

	public function test_it_returns_default_value_if_no_meta_set()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();

		$result = $metable->getMeta('foo', 'not-found');

		$this->assertEquals('not-found', $result);
	}

	public function test_it_can_replace_all_keys()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 'bar');

		$metable->syncMeta(['a' => 'b', 'c' => 'd']);
		$metable = $metable->fresh();

		$this->assertFalse($metable->hasMeta('foo'));
		$this->assertEquals('b', $metable->getMeta('a'));
		$this->assertEquals('d', $metable->getMeta('c'));
	}

	public function test_it_can_delete_meta()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 'bar');

		$metable->removeMeta('foo');

		$this->assertFalse($metable->hasMeta('foo'));
		$this->assertFalse($metable->fresh()->hasMeta('foo'));
	}

	public function test_it_can_be_queried_by_single_meta_key()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 'bar');

		$result = SampleMetable::whereHasMeta('foo')->first();

		$this->assertEquals($metable->getKey(), $result->getKey());
	}

	public function test_it_can_be_queried_by_any_meta_keys()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 'bar');
		$metable->setMeta('baz', 'bat');

		$result1 = SampleMetable::whereHasMeta(['foo', 'baz'])->first();
		$result2 = SampleMetable::whereHasMeta(['foo', 'zzz'])->first();

		$this->assertEquals($metable->getKey(), $result1->getKey());
		$this->assertEquals($metable->getKey(), $result2->getKey());
	}

	public function test_it_can_be_queried_by_all_meta_keys()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 'bar');
		$metable->setMeta('baz', 'bat');

		$result1 = SampleMetable::whereHasMetaKeys(['foo', 'baz'])->first();
		$result2 = SampleMetable::whereHasMetaKeys(['foo', 'zzz'])->first();

		$this->assertEquals($metable->getKey(), $result1->getKey());
		$this->assertNull($result2);
	}

	public function test_it_can_be_queried_by_meta_value()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 'bar');
		$metable->setMeta('array', ['a' => 'b']);

		$result1 = SampleMetable::whereMeta('foo', 'bar')->first();
		$result2 = SampleMetable::whereMeta('foo', 'baz')->first();
		$result3 = SampleMetable::whereMeta('array', ['a' => 'b'])->first();

		$this->assertEquals($metable->getKey(), $result1->getKey());
		$this->assertNull($result2);
		$this->assertEquals($metable->getKey(), $result3->getKey());
	}

	public function test_it_can_be_queried_by_numeric_meta_value()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 123);

		$result = SampleMetable::whereMeta('foo', '>', 4)->first();

		$this->assertEquals($metable->getKey(), $result->getKey());
	}

	public function test_it_can_be_queried_by_in_array()
	{
		$this->useDatabase();
		$metable = factory(SampleMetable::class)->create();
		$metable->setMeta('foo', 'bar');

		$result1 = SampleMetable::whereMetaIn('foo', ['baz', 'bar'])->first();
		$result2 = SampleMetable::whereMetaIn('foo', ['baz', 'bat'])->first();

		$this->assertEquals($metable->getKey(), $result1->getKey());
		$this->assertNull($result2);
	}
}