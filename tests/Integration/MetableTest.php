<?php

namespace Plank\Metable\Tests\Integration;

use Illuminate\Database\Eloquent\Collection;
use Plank\Metable\Meta;
use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\Mocks\SampleMetableSoftDeletes;
use Plank\Metable\Tests\TestCase;
use ReflectionClass;

class MetableTest extends TestCase
{
    public function test_it_can_get_and_set_meta_value_by_key()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->load('meta');
        $this->assertFalse($metable->hasMeta('foo'));

        $metable->setMeta('foo', 'bar');

        $this->assertTrue($metable->hasMeta('foo'));
        $this->assertEquals('bar', $metable->getMeta('foo'));

        $metable->setMeta('foo', 'baz');

        $this->assertTrue($metable->hasMeta('foo'));
        $this->assertEquals('baz', $metable->getMeta('foo'));
        $this->assertCount(1, $metable->meta);
    }

    public function test_it_can_set_many_meta_values_at_once()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->load('meta');
        $metable->setMeta('foo', 'old');
        $this->assertFalse($metable->hasMeta('bar'));
        $this->assertFalse($metable->hasMeta('baz'));

        $metable->setManyMeta([
                                  'foo' => 'bar',
                                  'bar' => 'baz',
                                  'baz' => ['foo', 'bar'],
                              ]);

        $this->assertTrue($metable->hasMeta('foo'));
        $this->assertTrue($metable->hasMeta('bar'));
        $this->assertTrue($metable->hasMeta('baz'));
        $this->assertEquals('bar', $metable->getMeta('foo'));
        $this->assertEquals('baz', $metable->getMeta('bar'));
        $this->assertEquals(['foo', 'bar'], $metable->getMeta('baz'));
    }

    public function test_it_accepts_empty_array_for_set_many_meta()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'old');
        $metable->setManyMeta([]); // should not error out
        $this->assertEquals('old', $metable->getMeta('foo'));
    }

    public function test_it_can_set_uppercase_key()
    {
        $this->useDatabase();
        $metable = $this->createMetable();

        $metable->setMeta('FOO', 'bar');

        $this->assertTrue($metable->hasMeta('FOO'));
        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertEquals('bar', $metable->getMeta('FOO'));
    }

    public function test_it_can_get_meta_record()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 123);

        $record = $metable->getMetaRecord('foo');

        $this->assertEquals('foo', $record->key);
        $this->assertEquals(123, $record->value);
    }

    public function test_it_can_get_meta_all_values()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 123);
        $metable->setMeta('bar', 'hello');
        $metable->setMeta('baz', ['a', 'b', 'c']);

        $collection = $metable->getAllMeta();

        $this->assertEquals([
                                'foo' => 123,
                                'bar' => 'hello',
                                'baz' => ['a', 'b', 'c'],
                            ], $collection->toArray());
    }

    public function test_it_updates_existing_meta_records()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
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
        $metable = $this->createMetable();

        $result = $metable->getMeta('foo', 'not-found');

        $this->assertEquals('not-found', $result);
    }

    public function test_it_can_replace_all_keys()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $metable->syncMeta(['a' => 'b', 'c' => 'd']);

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertEquals('b', $metable->getMeta('a'));
        $this->assertEquals('d', $metable->getMeta('c'));

        $metable = $metable->fresh(['meta']);

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertEquals('b', $metable->getMeta('a'));
        $this->assertEquals('d', $metable->getMeta('c'));
    }

    public function test_it_can_delete_meta()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $metable->removeMeta('foo');

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertFalse($metable->fresh()->hasMeta('foo'));
    }

    public function test_it_can_delete_meta_not_set()
    {
        $this->useDatabase();
        $metable = $this->createMetable();

        $metable->removeMeta('foo');

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertFalse($metable->fresh()->hasMeta('foo'));
    }

    public function test_it_can_delete_many_meta_at_once()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');
        $metable->setMeta('bar', 'baz');
        $metable->setMeta('baz', 'foo');

        $metable->removeManyMeta(['foo', 'bar', 'baz']);

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertFalse($metable->hasMeta('bar'));
        $this->assertFalse($metable->hasMeta('baz'));

        $metable = $metable->fresh();

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertFalse($metable->hasMeta('bar'));
        $this->assertFalse($metable->hasMeta('baz'));
    }

    public function test_it_can_delete_all_meta()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');
        $metable->setMeta('baz', 2);

        $metable->purgeMeta();

        $this->assertEquals(0, $metable->meta->count());
        $this->assertEquals(0, $metable->meta()->count());
    }

    public function test_it_clears_meta_on_deletion()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $metable->delete();
        $meta = Meta::all();

        $this->assertEquals(0, $meta->count());
    }

    public function test_it_does_not_clear_meta_on_soft_deletion()
    {
        $this->useDatabase();
        $metable = $this->createMetableSoftDeletes();
        $metable->setMeta('foo', 'bar');

        $metable->delete();
        $meta = Meta::all();

        $this->assertEquals(1, $meta->count());
    }

    public function test_it_does_clear_meta_on_force_deletion()
    {
        $this->useDatabase();
        $metable = $this->createMetableSoftDeletes();
        $metable->setMeta('foo', 'bar');

        $metable->forceDelete();
        $meta = Meta::all();

        $this->assertEquals(0, $meta->count());
    }

    public function test_it_can_be_queried_by_single_meta_key()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $result = SampleMetable::whereHasMeta('foo')->first();

        $this->assertEquals($metable->getKey(), $result->getKey());
    }

    public function test_it_can_retrieve_model_default_value()
    {
        $this->useDatabase();
        $result = $this->makeMetable();

        $this->assertEquals($result->getMeta('foo'), 'bar');
        $this->assertEquals(['foo' => 'bar'], $result->getAllMeta()->toArray());
    }

    public function test_it_can_get_database_before_default_value()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'baz');

        $result = SampleMetable::first();

        $this->assertEquals($result->getMeta('foo'), 'baz');
        $this->assertEquals(['foo' => 'baz'], $result->getAllMeta()->toArray());
    }

    public function test_it_can_get_passed_default_before_model_default_value()
    {
        $this->useDatabase();
        $this->createMetable();

        $result = SampleMetable::first();

        $this->assertEquals($result->getMeta('foo', null), null);
    }

    public function test_it_can_be_queried_by_missing_single_meta_key()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $metable2 = $this->createMetable();
        $metable2->setMeta('bar', 'foo');

        $result = SampleMetable::whereDoesntHaveMeta('foo')->first();

        $this->assertEquals($metable2->getKey(), $result->getKey());
        $this->assertNotEquals($metable->getKey(), $result->getKey());
    }

    public function test_it_can_be_queried_by_any_meta_keys()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');
        $metable->setMeta('baz', 'bat');

        $result1 = SampleMetable::whereHasMeta(['foo', 'baz'])->first();
        $result2 = SampleMetable::whereHasMeta(['foo', 'zzz'])->first();

        $this->assertEquals($metable->getKey(), $result1->getKey());
        $this->assertEquals($metable->getKey(), $result2->getKey());
    }

    public function test_it_can_be_queried_by_any_missing_meta_keys()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');
        $metable->setMeta('baz', 'bat');

        $metable2 = $this->createMetable();
        $metable2->setMeta('bee', 'bop');
        $metable2->setMeta('bop', 'bee');

        $result1 = SampleMetable::whereDoesntHaveMeta(['foo', 'baz'])->first();
        $result2 = SampleMetable::whereDoesntHaveMeta(['foo', 'zzz'])->first();

        $this->assertEquals($metable2->getKey(), $result1->getKey());
        $this->assertEquals($metable2->getKey(), $result2->getKey());

        $this->assertNotEquals($metable->getKey(), $result1->getKey());
        $this->assertNotEquals($metable->getKey(), $result2->getKey());
    }

    public function test_it_can_be_queried_by_all_meta_keys()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
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
        $metable = $this->createMetable();
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
        $metable = $this->createMetable();
        $metable->setMeta('foo', 123);

        $result = SampleMetable::whereMetaNumeric('foo', '>', 4)->first();

        $this->assertEquals($metable->getKey(), $result->getKey());
    }

    public function test_it_can_be_queried_by_in_array()
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $result1 = SampleMetable::whereMetaIn('foo', ['baz', 'bar'])->first();
        $result2 = SampleMetable::whereMetaIn('foo', ['baz', 'bat'])->first();

        $this->assertEquals($metable->getKey(), $result1->getKey());
        $this->assertNull($result2);
    }

    public function test_it_can_order_query_by_meta_value()
    {
        $this->useDatabase();
        $metable1 = $this->createMetable(['id' => 1]);
        $metable2 = $this->createMetable(['id' => 2]);
        $metable3 = $this->createMetable(['id' => 3]);
        $metable1->setMeta('foo', 'b');
        $metable2->setMeta('foo', 'c');
        $metable3->setMeta('foo', 'a');

        $results1 = SampleMetable::orderByMeta('foo', 'asc')->get();
        $results2 = SampleMetable::orderByMeta('foo', 'desc')->get();

        $this->assertEquals([3, 1, 2], $results1->pluck('id')->toArray());
        $this->assertEquals([2, 1, 3], $results2->pluck('id')->toArray());
    }

    public function test_it_can_order_query_by_meta_value_strict()
    {
        $this->useDatabase();
        $metable1 = $this->createMetable(['id' => 1]);
        $metable2 = $this->createMetable(['id' => 2]);
        $metable3 = $this->createMetable(['id' => 3]);
        $metable1->setMeta('foo', 'b');
        $metable2->setMeta('bar', 'c');
        $metable3->setMeta('foo', 'a');

        $results1 = SampleMetable::orderByMeta('foo', 'asc', true)->get();
        $results2 = SampleMetable::orderByMeta('foo', 'desc', true)->get();

        $this->assertEquals([3, 1], $results1->pluck('id')->toArray());
        $this->assertEquals([1, 3], $results2->pluck('id')->toArray());
    }

    public function test_it_can_order_query_by_numeric_meta_value()
    {
        $this->useDatabase();
        $metable1 = $this->createMetable(['id' => 1]);
        $metable2 = $this->createMetable(['id' => 2]);
        $metable3 = $this->createMetable(['id' => 3]);
        $metable1->setMeta('foo', 123);
        $metable2->setMeta('foo', 4);
        $metable3->setMeta('foo', 40);

        $results1 = SampleMetable::orderByMetaNumeric('foo', 'asc')->get();
        $results2 = SampleMetable::orderByMetaNumeric('foo', 'desc')->get();

        $this->assertEquals([2, 3, 1], $results1->pluck('id')->toArray());
        $this->assertEquals([1, 3, 2], $results2->pluck('id')->toArray());
    }

    public function test_it_can_order_query_by_numeric_meta_value_strict()
    {
        $this->useDatabase();
        $metable1 = $this->createMetable(['id' => 1]);
        $metable2 = $this->createMetable(['id' => 2]);
        $metable3 = $this->createMetable(['id' => 3]);
        $metable1->setMeta('foo', 123);
        $metable2->setMeta('bar', 4);
        $metable3->setMeta('foo', 40);

        $results1 = SampleMetable::orderByMetaNumeric('foo', 'asc', true)->get();
        $results2 = SampleMetable::orderByMetaNumeric('foo', 'desc', true)->get();

        $this->assertEquals([3, 1], $results1->pluck('id')->toArray());
        $this->assertEquals([1, 3], $results2->pluck('id')->toArray());
    }

    public function test_set_relation_updates_index()
    {
        $metable = $this->makeMetable();
        $meta = $this->makeMeta(['key' => 'foo', 'value' => 'bar']);
        $emptyCollection = new Collection();
        $metaCollection = new Collection([$meta]);
        $metable->setRelation('meta', $emptyCollection);

        $method = (new ReflectionClass($metable))
            ->getMethod('getMetaCollection');
        $method->setAccessible(true);

        $this->assertEquals($emptyCollection, $method->invoke($metable));

        $metable->setRelation('meta', $metaCollection);
        $this->assertEquals(new Collection(['foo' => $meta]), $method->invoke($metable));

        $metable->setRelation('other', $emptyCollection);
        $this->assertEquals(new Collection(['foo' => $meta]), $method->invoke($metable));

        $metable->setRelation('meta', $emptyCollection);
        $this->assertEquals($emptyCollection, $method->invoke($metable));
    }

    public function test_set_relations_updates_index()
    {
        $metable = $this->makeMetable();
        $meta = $this->makeMeta(['key' => 'foo', 'value' => 'bar']);
        $emptyCollection = new Collection();
        $metaCollection = new Collection([$meta]);
        $indexedCollection = new Collection(['foo' => $meta]);
        $metable->setRelation('meta', $emptyCollection);

        $method = (new ReflectionClass($metable))
            ->getMethod('getMetaCollection');
        $method->setAccessible(true);

        $this->assertEquals($emptyCollection, $method->invoke($metable));

        $metable->setRelations(['meta' => $metaCollection]);
        $this->assertEquals($indexedCollection, $method->invoke($metable));

        $metable->setRelations(['other' => $emptyCollection, 'meta' => $metaCollection]);
        $this->assertEquals($indexedCollection, $method->invoke($metable));

        $metable->setRelations(['meta' => $emptyCollection]);
        $this->assertEquals($emptyCollection, $method->invoke($metable));
    }

    public function test_it_can_serialize_properly()
    {
        $metable = $this->makeMetable();
        $meta = $this->makeMeta(['key' => 'foo', 'value' => 'baz']);
        $metaCollection = new Collection([$meta]);
        $metable->setRelation('meta', $metaCollection);
        /** @var SampleMetable $result */
        $result = unserialize(serialize($metable));
        $this->assertEquals('baz', $result->getMeta('foo'));
    }

    private function makeMeta(array $attributes = []): Meta
    {
        return factory(Meta::class)->make($attributes);
    }

    private function makeMetable(array $attributes = []): SampleMetable
    {
        return factory(SampleMetable::class)->make($attributes);
    }

    private function createMetable(array $attributes = []): SampleMetable
    {
        return factory(SampleMetable::class)->create($attributes);
    }

    private function createMetableSoftDeletes(array $attributes = []): SampleMetableSoftDeletes
    {
        return factory(SampleMetableSoftDeletes::class)->create($attributes);
    }
}
