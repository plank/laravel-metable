<?php

namespace Plank\Metable\Tests\Integration;

use Plank\Metable\Meta;
use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\Mocks\SampleMorph;
use Plank\Metable\Tests\TestCase;

class MorphTest extends TestCase
{
    public function test_it_can_get_and_set_meta_value_by_key()
    {
        $this->useDatabase();
        $child = $this->createChild();
        $this->assertFalse($child->hasMeta('foo'));

        $child->setMeta('foo', 'bar');

        $this->assertTrue($child->hasMeta('foo'));
        $this->assertEquals('bar', $child->getMeta('foo'));
    }

    public function test_it_can_get_meta_record()
    {
        $this->useDatabase();
        $child = $this->createChild();
        $child->setMeta('foo', 123);

        $class = SampleMetable::class;
        $record = $child->getMetaRecord('foo');

        $this->assertEquals('foo', $record->key);
        $this->assertEquals(123, $record->value);
        $this->assertEquals($class, $record->metable_type);
    }

    public function test_it_can_join_correctly_from_morphed_class()
    {
        $this->useDatabase();
        $metable1 = $this->createMetable(['id' => 1]);
        $child2 = $this->createChild(['id' => 2]);
        $child3 = $this->createChild(['id' => 3]);
        $metable1->setMeta('foo', 'b');
        $child2->setMeta('foo', 'c');
        $child3->setMeta('foo', 'a');

        $class = SampleMetable::class;
        $results1 = SampleMorph::orderByMeta('foo', 'asc')->get();
        $results2 = Meta::select('metable_type')->get();

        $this->assertCount(3, $results1->pluck('id')->toArray());
        $this->assertEquals([$class, $class, $class], $results2->pluck('metable_type')->toArray());
    }

    private function createMetable(array $attributes = []): SampleMetable
    {
        return factory(SampleMetable::class)->create($attributes);
    }

    private function createChild(array $attributes = []): SampleMorph
    {
        return factory(SampleMorph::class)->create($attributes);
    }
}
