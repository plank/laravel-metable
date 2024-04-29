<?php

namespace Plank\Metable\Tests\Integration;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsStringable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Stringable;
use Plank\Metable\Exceptions\CastException;
use Plank\Metable\Meta;
use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\Mocks\SampleMetableSoftDeletes;
use Plank\Metable\Tests\Mocks\SampleSerializable;
use Plank\Metable\Tests\TestCase;
use ReflectionClass;

class MetableTest extends TestCase
{
    public function test_it_can_get_and_set_meta_value_by_key(): void
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

    public function test_it_can_set_meta_encrypted(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->load('meta');
        $this->assertFalse($metable->hasMeta('foo'));

        $metable->setMeta('foo', 'bar', true);

        $this->assertTrue($metable->hasMeta('foo'));
        $this->assertEquals('bar', $metable->getMeta('foo'));
        $this->assertEquals('encrypted:string', $metable->getMetaRecord('foo')->type);

        $metable->setMetaEncrypted('baz', [1 => 2]);

        $this->assertTrue($metable->hasMeta('baz'));
        $this->assertEquals([1 => 2], $metable->getMeta('baz'));
        $this->assertEquals('encrypted:serialized', $metable->getMetaRecord('baz')->type);
    }

    public function test_it_can_set_many_meta_values_at_once(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->load('meta');
        $metable->setMeta('foo', 'old');
        $this->assertFalse($metable->hasMeta('bar'));
        $this->assertFalse($metable->hasMeta('baz'));

        $metable->setManyMeta([
            'foo' => 'bar',
            'bar' => 33,
            'baz' => ['foo', 'bar'],
        ]);

        $this->assertTrue($metable->hasMeta('foo'));
        $this->assertTrue($metable->hasMeta('bar'));
        $this->assertTrue($metable->hasMeta('baz'));
        $this->assertEquals('bar', $metable->getMeta('foo'));
        $this->assertEquals(33, $metable->getMeta('bar'));
        $this->assertEquals(['foo', 'bar'], $metable->getMeta('baz'));

        $this->assertEquals(33, $metable->getMetaRecord('bar')->numeric_value);
        $this->assertNotEmpty($metable->getMetaRecord('baz')->hmac);
    }

    public function test_it_accepts_empty_array_for_set_many_meta(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'old');
        $metable->setManyMeta([]); // should not error out
        $this->assertEquals('old', $metable->getMeta('foo'));
    }

    public function test_it_can_set_uppercase_key(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();

        $metable->setMeta('FOO', 'bar');

        $this->assertTrue($metable->hasMeta('FOO'));
        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertEquals('bar', $metable->getMeta('FOO'));
    }

    public function test_it_can_get_meta_record(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 123);

        $record = $metable->getMetaRecord('foo');

        $this->assertEquals('foo', $record->key);
        $this->assertEquals(123, $record->value);
    }

    public function test_it_can_get_meta_all_values(): void
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

    public function test_it_updates_existing_meta_records(): void
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

    public function test_it_returns_default_value_if_no_meta_set(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();

        $result = $metable->getMeta('foo', 'not-found');

        $this->assertEquals('not-found', $result);
    }

    public function test_it_can_replace_all_keys(): void
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

    public function test_it_can_delete_meta(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $metable->removeMeta('foo');

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertFalse($metable->fresh()->hasMeta('foo'));
    }

    public function test_it_can_delete_meta_not_set(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();

        $metable->removeMeta('foo');

        $this->assertFalse($metable->hasMeta('foo'));
        $this->assertFalse($metable->fresh()->hasMeta('foo'));
    }

    public function test_it_can_delete_many_meta_at_once(): void
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

    public function test_it_can_delete_all_meta(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');
        $metable->setMeta('baz', 2);

        $metable->purgeMeta();

        $this->assertEquals(0, $metable->meta->count());
        $this->assertEquals(0, $metable->meta()->count());
    }

    public function test_it_clears_meta_on_deletion(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $metable->delete();
        $meta = Meta::all();

        $this->assertEquals(0, $meta->count());
    }

    public function test_it_does_not_clear_meta_on_soft_deletion(): void
    {
        $this->useDatabase();
        $metable = $this->createMetableSoftDeletes();
        $metable->setMeta('foo', 'bar');

        $metable->delete();
        $meta = Meta::all();

        $this->assertEquals(1, $meta->count());
    }

    public function test_it_does_clear_meta_on_force_deletion(): void
    {
        $this->useDatabase();
        $metable = $this->createMetableSoftDeletes();
        $metable->setMeta('foo', 'bar');

        $metable->forceDelete();
        $meta = Meta::all();

        $this->assertEquals(0, $meta->count());
    }

    public function test_it_can_be_queried_by_single_meta_key(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $result = SampleMetable::whereHasMeta('foo')->first();

        $this->assertEquals($metable->getKey(), $result->getKey());
    }

    public function test_it_can_retrieve_model_default_value(): void
    {
        $this->useDatabase();
        $result = $this->makeMetable();

        $this->assertEquals($result->getMeta('foo'), 'bar');
        $this->assertEquals(['foo' => 'bar'], $result->getAllMeta()->toArray());
    }

    public function test_it_can_get_database_before_default_value(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'baz');

        $result = SampleMetable::first();

        $this->assertEquals($result->getMeta('foo'), 'baz');
        $this->assertEquals(['foo' => 'baz'], $result->getAllMeta()->toArray());
    }

    public function test_it_can_get_passed_default_before_model_default_value(): void
    {
        $this->useDatabase();
        $this->createMetable();

        $result = SampleMetable::first();

        $this->assertEquals($result->getMeta('foo', null), null);
    }

    public function test_it_can_be_queried_by_missing_single_meta_key(): void
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

    public function test_it_can_be_queried_by_any_meta_keys(): void
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

    public function test_it_can_be_queried_by_any_missing_meta_keys(): void
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

    public function test_it_can_be_queried_by_all_meta_keys(): void
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

    public function test_it_can_be_queried_by_meta_value(): void
    {
        $now = Carbon::now();
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');
        $metable->setMeta('datetime', $now);
        $metable->setMeta('long', str_repeat('a', 300));
        $metable->setMeta('empty', '');
        $metable->setMeta('null', null);

        $result1 = SampleMetable::whereMeta('foo', 'bar')->first();
        $result2 = SampleMetable::whereMeta('foo', 'baz')->first();
        $result3 = SampleMetable::whereMeta('datetime', $now)->first();
        $result4 = SampleMetable::whereMeta('long', str_repeat('a', 300))->first();

        $result5 = SampleMetable::whereMeta('empty', '')->first();
        $result6 = SampleMetable::whereMeta('empty', null)->first();
        $result7 = SampleMetable::whereMeta('null', '')->first();
        $result8 = SampleMetable::whereMeta('null', null)->first();

        $this->assertEquals($metable->getKey(), $result1->getKey());
        $this->assertNull($result2);
        $this->assertEquals($metable->getKey(), $result3->getKey());
        $this->assertEquals($metable->getKey(), $result4->getKey());
        $this->assertEquals($metable->getKey(), $result5->getKey());
        $this->assertNull($result6);
        $this->assertNull($result7);
        $this->assertEquals($metable->getKey(), $result8->getKey());
    }

    public function test_it_can_be_queried_by_numeric_meta_value(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 123);

        $result = SampleMetable::whereMetaNumeric('foo', '>', 4)->get();
        $result2 = SampleMetable::whereMetaNumeric('foo', '<', 4)->get();
        $result3 = SampleMetable::whereMetaNumeric('foo', 123)->get();

        $this->assertEquals([$metable->getKey()], $result->modelKeys());
        $this->assertEquals([], $result2->modelKeys());
        $this->assertEquals([$metable->getKey()], $result3->modelKeys());
    }

    public function test_it_can_be_queried_by_in_array(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $result1 = SampleMetable::whereMetaIn('foo', ['baz', 'bar'])->get();
        $result2 = SampleMetable::whereMetaIn('foo', ['baz', 'bat'])->get();

        $this->assertEquals([$metable->getKey()], $result1->modelKeys());
        $this->assertEquals([], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_not_in_array(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'bar');

        $result1 = SampleMetable::whereMetaNotIn('foo', ['baz', 'bar'])->get();
        $result2 = SampleMetable::whereMetaNotIn('foo', ['baz', 'bat'])->get();

        $this->assertEquals([], $result1->modelKeys());
        $this->assertEquals([$metable->getKey()], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_in_array_numeric(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 1.1);

        $result1 = SampleMetable::whereMetaInNumeric('foo', [1.1, 2.2])->get();
        $result2 = SampleMetable::whereMetaInNumeric('foo', [1, 2])->get();

        $this->assertEquals([$metable->getKey()], $result1->modelKeys());
        $this->assertEquals([], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_not_in_array_numeric(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 1.1);

        $result1 = SampleMetable::whereMetaNotInNumeric('foo', [1.1, 2.2])->get();
        $result2 = SampleMetable::whereMetaNotInNumeric('foo', [1, 2])->get();

        $this->assertEquals([], $result1->modelKeys());
        $this->assertEquals([$metable->getKey()], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_meta_between(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'c');

        $result1 = SampleMetable::whereMetaBetween(
            'foo',
            'a',
            'd'
        )->get();
        $result2 = SampleMetable::whereMetaBetween(
            'foo',
            'd',
            'z'
        )->get();

        $this->assertEquals([$metable->getKey()], $result1->modelKeys());
        $this->assertEquals([], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_meta_not_between(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->setMeta('foo', 'c');

        $result1 = SampleMetable::whereMetaNotBetween(
            'foo',
            'a',
            'd'
        )->get();
        $result2 = SampleMetable::whereMetaNotBetween(
            'foo',
            'd',
            'z'
        )->get();

        $this->assertEquals([], $result1->modelKeys());
        $this->assertEquals([$metable->getKey()], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_meta_between_numeric(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $date = Carbon::now();
        $metable->setMeta('foo', $date);

        $result1 = SampleMetable::whereMetaBetweenNumeric(
            'foo',
            $date->clone()->subDay(),
            $date->clone()->addDay()
        )->get();
        $result2 = SampleMetable::whereMetaBetweenNumeric(
            'foo',
            $date->subDays(2),
            $date->subDay()
        )->get();

        $this->assertEquals([$metable->getKey()], $result1->modelKeys());
        $this->assertEquals([], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_meta_not_between_numeric(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $date = Carbon::now();
        $metable->setMeta('foo', $date);

        $result1 = SampleMetable::whereMetaNotBetweenNumeric(
            'foo',
            $date->clone()->subDay(),
            $date->clone()->addDay()
        )->get();
        $result2 = SampleMetable::whereMetaNotBetweenNumeric(
            'foo',
            $date->subDays(2),
            $date->subDay()
        )->get();

        $this->assertEquals([], $result1->modelKeys());
        $this->assertEquals([$metable->getKey()], $result2->modelKeys());
    }

    public function test_it_can_be_queried_by_null(): void
    {
        $this->useDatabase();
        $metable1 = $this->createMetable();
        $metable1->setMeta('foo', null);

        $metable2 = $this->createMetable();
        $metable2->setMeta('foo', 1);

        $result1 = SampleMetable::whereMetaIsNull('foo')->get();

        $this->assertEquals([$metable1->getKey()], $result1->modelKeys());
    }

    public function test_it_can_be_queried_by_model(): void
    {
        $this->useDatabase();
        $metable1 = $this->createMetable();
        $metable2 = $this->createMetable();
        $metable1->setMeta('foo', $metable2);
        $metable2->setMeta('foo', $metable1);

        $result1 = SampleMetable::whereMetaIsModel('foo', $metable1)->get();
        $result2 = SampleMetable::whereMetaIsModel('foo', $metable2)->get();
        $result3 = SampleMetable::whereMetaIsModel(
            'foo',
            SampleMetable::class,
            $metable1->getKey()
        )->get();
        $result4 = SampleMetable::whereMetaIsModel(
            'foo',
            SampleMetable::class,
            $metable2->getKey()
        )->get();
        $result5 = SampleMetable::whereMetaIsModel('foo', SampleMetable::class)->get();

        $this->assertEquals([$metable2->getKey()], $result1->modelKeys());
        $this->assertEquals([$metable1->getKey()], $result2->modelKeys());
        $this->assertEquals([$metable2->getKey()], $result3->modelKeys());
        $this->assertEquals([$metable1->getKey()], $result4->modelKeys());
        $this->assertEquals([$metable1->getKey(), $metable2->getKey()], $result5->modelKeys());
    }

    public function test_it_can_order_query_by_meta_value(): void
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

        $this->assertEquals([3, 1, 2], $results1->modelKeys());
        $this->assertEquals([2, 1, 3], $results2->modelKeys());
    }

    public function test_it_can_order_query_by_meta_value_strict(): void
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

        $this->assertEquals([3, 1], $results1->modelKeys());
        $this->assertEquals([1, 3], $results2->modelKeys());
    }

    public function test_it_can_order_query_by_numeric_meta_value(): void
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

        $this->assertEquals([2, 3, 1], $results1->modelKeys());
        $this->assertEquals([1, 3, 2], $results2->modelKeys());
    }

    public function test_it_can_order_query_by_numeric_meta_value_strict(): void
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

        $this->assertEquals([3, 1], $results1->modelKeys());
        $this->assertEquals([1, 3], $results2->modelKeys());
    }

    public function test_it_can_query_long_strings(): void
    {
        config()->set('metable.stringValueIndexLength', 255);
        $this->useDatabase();
        $metable1 = $this->createMetable();
        $metable1->setMeta('foo', $val1 = str_repeat('a', 255) . 'm');
        $metable2 = $this->createMetable();
        $metable2->setMeta('foo', $val2 = str_repeat('a', 255) . 'f');

        $this->assertSame(
            [$metable1->getKey()],
            SampleMetable::whereMeta('foo', $val1)->get()->modelKeys()
        );
        $this->assertSame(
            [$metable2->getKey()],
            SampleMetable::whereMeta('foo', $val2)->get()->modelKeys()
        );

        $this->assertSame(
            [$metable1->getKey()],
            SampleMetable::whereMetaIn('foo', [$val1])->get()->modelKeys()
        );

        $this->assertSame(
            [$metable2->getKey()],
            SampleMetable::whereMetaIn('foo', [$val2])->get()->modelKeys()
        );

        $this->assertSame(
            [$metable1->getKey(), $metable2->getKey()],
            SampleMetable::whereMetaIn('foo', [$val1, $val2])->get()->modelKeys()
        );

        $this->assertSame(
            [$metable2->getKey()],
            SampleMetable::whereMetaBetween(
                'foo',
                str_repeat('a', 256),
                str_repeat('a', 255) . 'l'
            )->get()->modelKeys()
        );

        $this->assertSame(
            [$metable1->getKey()],
            SampleMetable::whereMetaBetween(
                'foo',
                str_repeat('a', 255) . 'm',
                str_repeat('a', 255) . 'z'
            )->get()->modelKeys()
        );

        $this->assertSame(
            [$metable2->getKey(), $metable1->getKey()],
            SampleMetable::orderByMeta('foo', 'asc')->get()->modelKeys()
        );

        $this->assertSame(
            [$metable1->getKey(), $metable2->getKey()],
            SampleMetable::orderByMeta('foo', 'desc')->get()->modelKeys()
        );
    }

    public function test_set_relation_updates_index(): void
    {
        $metable = $this->makeMetable();
        $meta = $this->makeMeta(['key' => 'foo', 'value' => 'bar']);
        $emptyCollection = new Collection();
        $metaCollection = new Collection([$meta]);
        $metable->setRelation('meta', $emptyCollection);

        $method = (new ReflectionClass($metable))
            ->getMethod('getMetaCollection');

        $this->assertEquals($emptyCollection, $method->invoke($metable));

        $metable->setRelation('meta', $metaCollection);
        $this->assertEquals(new Collection(['foo' => $meta]), $method->invoke($metable));

        $metable->setRelation('other', $emptyCollection);
        $this->assertEquals(new Collection(['foo' => $meta]), $method->invoke($metable));

        $metable->setRelation('meta', $emptyCollection);
        $this->assertEquals($emptyCollection, $method->invoke($metable));
    }

    public function test_set_relations_updates_index(): void
    {
        $metable = $this->makeMetable();
        $meta = $this->makeMeta(['key' => 'foo', 'value' => 'bar']);
        $emptyCollection = new Collection();
        $metaCollection = new Collection([$meta]);
        $indexedCollection = new Collection(['foo' => $meta]);
        $metable->setRelation('meta', $emptyCollection);

        $method = (new ReflectionClass($metable))
            ->getMethod('getMetaCollection');

        $this->assertEquals($emptyCollection, $method->invoke($metable));

        $metable->setRelations(['meta' => $metaCollection]);
        $this->assertEquals($indexedCollection, $method->invoke($metable));

        $metable->setRelations(['other' => $emptyCollection, 'meta' => $metaCollection]);
        $this->assertEquals($indexedCollection, $method->invoke($metable));

        $metable->setRelations(['meta' => $emptyCollection]);
        $this->assertEquals($emptyCollection, $method->invoke($metable));
    }

    public function test_it_can_serialize_properly(): void
    {
        $metable = $this->makeMetable();
        $meta = $this->makeMeta(['key' => 'foo', 'value' => 'baz']);
        $metaCollection = new Collection([$meta]);
        $metable->setRelation('meta', $metaCollection);
        /** @var SampleMetable $result */
        $result = unserialize(serialize($metable));
        $this->assertEquals('baz', $result->getMeta('foo'));
    }

    public function test_it_throws_for_param_that_cannot_be_converted_to_numeric(): void
    {
        $this->expectException(\LogicException::class);
        SampleMetable::query()->whereMetaNumeric('foo', null)->get();
    }

    public static function castProvider(): array
    {
        $date = Carbon::now();
        $object = new \stdClass();
        $object->foo = 'bar';
        $model = new SampleMetable();
        $model->id = 99;
        $model->exists = true;
        $modelCollection = new Collection([$model]);
        return [
            'string - string' => ['string', 'foo', 'foo', 'string'],
            'string - int' => ['string', 123, '123', 'string'],
            'string - float' => ['string', 123.45, '123.45', 'string'],
            'string - true' => ['string', true, '1', 'string'],
            'string - false' => ['string', false, '', 'string'],
            'string - null' => ['string', null, null, 'null'],
            'string - dateTime' => ['string', $date, (string)$date, 'string'],
            'array - array' => ['array', ['foo', 'bar'], ['foo', 'bar'], 'serialized'],
            'array - json' => [
                'array',
                json_encode(['foo' => 'bar']),
                ['foo' => 'bar'],
                'serialized'
            ],
            'array - object' => ['array', $object, ['foo' => 'bar'], 'serialized'],
            'array - null' => ['array', null, null, 'null'],
            'boolean - true' => ['boolean', true, true, 'boolean'],
            'boolean - false' => ['boolean', false, false, 'boolean'],
            'boolean - 1' => ['boolean', 1, true, 'boolean'],
            'boolean - 0' => ['boolean', 0, false, 'boolean'],
            'boolean - null' => ['boolean', null, null, 'null'],
            'boolean - string' => ['boolean', 'abc', true, 'boolean'],
            'boolean - empty string' => ['boolean', '', false, 'boolean'],
            'boolean - string 1' => ['boolean', '1', true, 'boolean'],
            'boolean - string 0' => ['boolean', '0', false, 'boolean'],
            'decimal - int' => ['decimal:2', 123, '123.00', 'string'],
            'decimal - float' => ['decimal:2', 123.456, '123.46', 'string'],
            'decimal - string' => ['decimal:2', '123.456', '123.46', 'string'],
            'decimal - null' => ['decimal:2', null, null, 'null'],
            'double - int' => ['double', 123, 123.0, 'float'],
            'double - float' => ['double', 123.456, 123.456, 'float'],
            'double - string' => ['double', '123.456', 123.456, 'float'],
            'double - string int' => ['double', '123', 123.0, 'float'],
            'double - null' => ['double', null, null, 'null'],
            'float - int' => ['float', 123, 123.0, 'float'],
            'float - float' => ['float', 123.456, 123.456, 'float'],
            'float - string' => ['float', '123.456', 123.456, 'float'],
            'float - string int' => ['float', '123', 123.0, 'float'],
            'float - null' => ['float', null, null, 'null'],
            'integer - int' => ['integer', 123, 123, 'integer'],
            'integer - float' => ['integer', 123.456, 123, 'integer'],
            'integer - string' => ['integer', '123', 123, 'integer'],
            'integer - null' => ['integer', null, null, 'null'],
            'object - object' => ['object', $object, $object, 'serialized', false],
            'object - array' => [
                'object',
                ['foo' => 'bar'],
                $object,
                'serialized',
                false
            ],
            'object - json' => [
                'object',
                json_encode(['foo' => 'bar']),
                $object,
                'serialized',
                false
            ],
            'object - null' => ['object', null, null, 'null'],
            'real - int' => ['real', 123, 123.0, 'float'],
            'real - float' => ['real', 123.456, 123.456, 'float'],
            'real - string' => ['real', '123.456', 123.456, 'float'],
            'real - string int' => ['real', '123', 123.0, 'float'],
            'real - null' => ['real', null, null, 'null'],
            'timestamp - dateTime' => ['timestamp', $date, $date->timestamp, 'integer'],
            'timestamp - int' => ['timestamp', 123, 123, 'integer'],
            'timestamp - string' => [
                'timestamp',
                '2020-01-01 00:00:00',
                strtotime('2020-01-01 00:00:00'),
                'integer'
            ],
            'timestamp - null' => ['timestamp', null, null, 'null'],
            'date - dateTime' => [
                'date',
                $date,
                $date->copy()->startOfDay(),
                'datetime',
                false
            ],
            'date - string' => [
                'date',
                (string)$date,
                $date->copy()->startOfDay(),
                'datetime',
                false
            ],
            'date - timestamp' => [
                'date',
                $date->timestamp,
                $date->copy()->startOfDay(),
                'datetime',
                false
            ],
            'date - string timestamp' => [
                'date',
                (string)$date->timestamp,
                $date->copy()->startOfDay(),
                'datetime',
                false
            ],
            'date - null' => ['date', null, null, 'null'],
            'datetime - dateTime' => ['datetime', $date, $date, 'datetime', false],
            'datetime - string' => [
                'datetime',
                $date->format('Y-m-d H:i:s.uO'),
                $date,
                'datetime',
                false
            ],
            'datetime - timestamp' => [
                'datetime',
                $date->timestamp,
                $date->copy()->startOfSecond(),
                'datetime',
                false
            ],
            'datetime - string timestamp' => [
                'datetime',
                (string)$date->timestamp,
                $date->copy()->startOfSecond(),
                'datetime',
                false
            ],
            'datetime - null' => ['datetime', null, null, 'null'],
            'immutable_date - dateTime' => [
                'immutable_date',
                $date,
                $date->copy()->startOfDay()->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_date - string' => [
                'immutable_date',
                $date->format('Y-m-d H:i:s.uO'),
                $date->copy()->startOfDay()->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_date - timestamp' => [
                'immutable_date',
                $date->timestamp,
                $date->copy()->startOfDay()->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_date - string timestamp' => [
                'immutable_date',
                (string)$date->timestamp,
                $date->copy()->startOfDay()->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_date - null' => ['immutable_date', null, null, 'null'],
            'immutable_datetime - dateTime' => [
                'immutable_datetime',
                $date,
                $date->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_datetime - string' => [
                'immutable_datetime',
                $date->format('Y-m-d H:i:s.uO'),
                $date->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_datetime - timestamp' => [
                'immutable_datetime',
                $date->timestamp,
                $date->copy()->startOfSecond()->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_datetime - string timestamp' => [
                'immutable_datetime',
                (string)$date->timestamp,
                $date->copy()->startOfSecond()->toImmutable(),
                'datetime_immutable',
                false
            ],
            'immutable_datetime - null' => ['immutable_datetime', null, null, 'null'],
            'hashed - string' => [
                'hashed',
                'foo',
                fn ($result) => password_verify('foo', $result),
                'string'
            ],
            'hashed - int' => [
                'hashed',
                123,
                fn ($result) => password_verify('123', $result),
                'string'
            ],
            'hashed - null' => ['hashed', null, null, 'null'],
            'collection - array' => [
                'collection',
                ['foo', 'bar'],
                collect(['foo', 'bar']),
                'serialized',
                false
            ],
            'collection - eloquent' => [
                'collection',
                $model,
                fn ($result) => $result->modelKeys() === $modelCollection->modelKeys(),
                'collection',
                false
            ],
            'collection - eloquent collection' => [
                'collection',
                $modelCollection,
                fn ($result) => $result->modelKeys() === $modelCollection->modelKeys(),
                'collection',
                false
            ],
            'collection - null' => ['collection', null, null, 'null'],
            'collection:class - eloquent object' => [
                'collection:' . SampleMetable::class,
                $model,
                fn ($result) => $result->modelKeys() === $modelCollection->modelKeys(),
                'collection',
                false
            ],
            'collection:class - eloquent array' => [
                'collection:' . SampleMetable::class,
                [$model],
                fn ($result) => $result->modelKeys() === $modelCollection->modelKeys(),
                'collection',
                false
            ],
            'collection:class - eloquent collection' => [
                'collection:' . SampleMetable::class,
                $modelCollection,
                fn ($result) => $result->modelKeys() === $modelCollection->modelKeys(),
                'collection',
                false
            ],
            'collection:class - object collection' => [
                'collection:' . \stdClass::class,
                collect([$object]),
                collect([$object]),
                'serialized',
                false
            ],
            'stringable - string' => [
                AsStringable::class,
                'foo',
                new Stringable('foo'),
                'stringable',
                false
            ],
            'stringable - int' => [
                AsStringable::class,
                123,
                new Stringable('123'),
                'stringable',
                false
            ],
            'stringable - null' => [AsStringable::class, null, null, 'null'],
            'encrypted - string' => ['encrypted', 'foo', 'foo', 'encrypted:string'],
            'encrypted - array' => [
                'encrypted',
                ['foo' => 'bar'],
                ['foo' => 'bar'],
                'encrypted:serialized'
            ],
            'encrypted - null' => ['encrypted', null, null, 'null'],
            'encrypted:collection - array' => [
                'encrypted:collection',
                ['foo', 'bar'],
                collect(['foo', 'bar']),
                'encrypted:serialized',
                false
            ],
            'encrypted:collection - eloquent' => [
                'encrypted:collection',
                $model,
                fn ($result) => $result->modelKeys() === $modelCollection->modelKeys(),
                'encrypted:collection',
                false
            ],
            'encrypted:collection - eloquent collection' => [
                'encrypted:collection',
                $modelCollection,
                fn ($result) => $result->modelKeys() === $modelCollection->modelKeys(),
                'encrypted:collection',
                false
            ],
            'encrypted:string - int' => [
                'encrypted:string',
                123,
                '123',
                'encrypted:string'
            ],
            'class - class' => [\stdClass::class, $object, $object, 'serialized', false],
            'class - eloquent id' => [
                SampleMetable::class,
                99,
                fn ($result) => $result instanceof SampleMetable
                    && $result->getKey() === $model->getKey(),
                'model',
                false
            ],
        ];
    }

    /** @dataProvider castProvider */
    public function test_it_casts_meta_values(
        string $cast,
        mixed $original,
        mixed $expected,
        string $expectedHandlerType,
        bool $strict = true
    ): void {
        $this->useDatabase();

        if ($cast === 'collection'
            || $cast === 'encrypted:collection'
            || str_starts_with($cast, 'collection:')
            || $cast === SampleMetable::class
        ) {
            $model = new SampleMetable();
            $model->id = 99;
            $model->save();
        }

        $key = 'castable';
        $metable = $this->createMetable();
        $metable->mergeMetaCasts([$key => $cast]);
        $metable->setMeta($key, $original);
        if ($expected instanceof \Closure) {
            $this->assertTrue($expected($metable->getMeta($key)));
        } elseif ($strict) {
            $this->assertSame($expected, $metable->getMeta($key));
        } else {
            $this->assertEquals($expected, $metable->getMeta($key));
        }
        $this->assertSame($expectedHandlerType, $metable->getMetaRecord($key)->type);
    }

    public static function invalidClassCastProvider(): array
    {
        return [
            'collection:class - string' => ['collection:stdClass', collect('bar')],
            'collection:class - int' => ['collection:stdClass', collect(123)],
            'collection:class - other class' => ['collection:stdClass', collect(new SampleSerializable([]))],
            'class - string' => [\stdClass::class, 'bar'],
            'class - int' => [\stdClass::class, 123],
            'class - other class' => [\stdClass::class, new SampleSerializable([])],
            'eloquent - int' => [SampleMetable::class, 999, ModelNotFoundException::class],
            'eloquent - string' => [SampleMetable::class, 'abc', ModelNotFoundException::class],
            'eloquent - other class' => [SampleMetable::class, new \stdClass()],
        ];
    }

    public function test_cast_source_hierarchy(): void
    {
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->metaCasts = [
            'prop_cast' => 'string',
            'cast' => 'string',
        ];
        $metable->methodMetaCasts = ['cast' => 'integer'];

        $metable->setMeta('prop_cast', 123);
        $this->assertSame('123', $metable->getMeta('prop_cast'));
        $metable->setMeta('cast', 123);
        $this->assertSame(123, $metable->getMeta('cast'));

        $date = Carbon::now()->startOfSecond();
        $metable->mergeMetaCasts(['prop_cast' => 'datetime', 'cast' => 'datetime']);
        $metable->setMeta('prop_cast', $date->timestamp);
        $metable->setMeta('cast', $date->timestamp);
        $this->assertEquals($date, $metable->getMeta('prop_cast'));
        $this->assertEquals($date, $metable->getMeta('cast'));
    }

    /** @dataProvider invalidClassCastProvider */
    public function test_it_throws_for_invalid_class_cast(
        string $cast,
        mixed $invalidValue,
        string $expectedException = CastException::class
    ): void {
        $this->expectException($expectedException);
        $this->useDatabase();
        $metable = $this->createMetable();
        $metable->mergeMetaCasts(['foo' => $cast]);
        $metable->setMeta('foo', $invalidValue);
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
