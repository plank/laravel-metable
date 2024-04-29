<?php

namespace Plank\Metable\Tests\Integration;

use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\TestCase;

class MetableAttributesTest extends TestCase
{
    public function test_it_mutates_meta_attributes()
    {
        $this->useDatabase();

        $model = $this->createMetable();
        $this->assertFalse($model->hasMeta('var'));
        $this->assertFalse(isset($model->meta_var));
        $this->assertNull($model->getAttribute('meta_var'));

        $model->setAttribute('meta_var', 'bar');
        $this->assertEquals('bar', $model->getMeta('var'));
        $this->assertTrue(isset($model->meta_var));
        $this->assertEquals('bar', $model->getAttribute('meta_var'));

        $model->meta_var = 'baz';
        $this->assertEquals('baz', $model->getMeta('var'));
        $this->assertTrue(isset($model->meta_var));
        $this->assertEquals('baz', $model->getAttribute('meta_var'));

        $model->offsetUnset('meta_var');
        $this->assertFalse($model->hasMeta('var'));
        $this->assertFalse(isset($model->meta_var));
        $this->assertNull($model->getAttribute('meta_var'));

        $model->fill(['meta_var' => 'qux']);
        $this->assertEquals('qux', $model->getMeta('var'));
        $this->assertTrue(isset($model->meta_var));
        $this->assertEquals('qux', $model->getAttribute('meta_var'));

        unset($model['meta_var']);
        $this->assertFalse($model->hasMeta('var'));
        $this->assertFalse(isset($model->meta_var));
        $this->assertNull($model->getAttribute('meta_var'));
    }

    public function test_it_doesnt_overwrite_existing_attributes()
    {
        $this->useDatabase();

        $model = $this->createMetable();
        $model->meta_attribute = 'bar';
        $this->assertFalse($model->hasMeta('attribute'));

        $model->setAttribute('meta_attribute', 'baz');
        $this->assertFalse($model->hasMeta('attribute'));

        $model->meta_attribute = 'qux';
        $this->assertTrue($model->offsetExists('meta_attribute'));
        $model->offsetUnset('meta_attribute');
        $this->assertNull($model->meta_attribute);
    }

    public function test_it_converts_to_array()
    {
        $this->useDatabase();
        $model = $this->createMetable();
        $model->meta_var = 'foo';
        $model->meta_var2 = 'foo2';

        $this->assertEquals(
            collect([
                'meta_var' => 'foo',
                'meta_var2' => 'foo2',
                'meta_foo' => 'bar' // default value
            ]),
            $model->getMetaAttributes()
        );

        $model->makeHidden('meta_var2', 'created_at', 'updated_at', 'meta');

        $this->assertEquals([
            'meta_attribute' => '',
            'id' => $model->getKey(),
            'meta_foo' => 'bar',
            'meta_var' => 'foo'
        ], $model->toArray());

        $model->includeMetaInArray = false;
        $this->assertEquals([
            'meta_attribute' => '',
            'id' => $model->getKey(),
        ], $model->toArray());

        $model->includeMetaInArray = true;
        $model->makeMetaHidden();

        $this->assertEquals([
            'meta_attribute' => '',
            'id' => $model->getKey(),
        ], $model->toArray());
    }

    private function createMetable(array $attributes = []): SampleMetable
    {
        return factory(SampleMetable::class)->create($attributes);
    }
}
