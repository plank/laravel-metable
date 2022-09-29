<?php

namespace Plank\Metable\Tests\Integration;

use Plank\Metable\Exceptions\DataTypeException;
use Plank\Metable\Tests\Mocks\SampleMetableTypes;
use Plank\Metable\Tests\TestCase;

class DefaultHandlersMetableTest extends TestCase
{
    public function test_it_parses_given_variables_according_to_given_metaCasts()
    {
        $this->useDatabase();
        $metable = $this->createMetable();

        $metable->setMeta('fooBool', 'oooo');
        $this->assertEquals(true, $metable->getMeta('fooBool'));

        $metable->setMeta('fooBool', '');
        $this->assertEquals(false, $metable->getMeta('fooBool'));

        $metable->setMeta('fooString', 1234);
        $this->assertEquals('1234', $metable->getMeta('fooString'));

        $metable->setMeta('random', 1234); // auto choose
        $this->assertEquals(1234, $metable->getMeta('random'));
    }

    public function test_it_throws_exception_if_metaCast_value_is_not_a_handler()
    {
        $this->useDatabase();
        $metable = $this->createMetable();

        $this->expectException(DataTypeException::class);

        $metable->setMeta('fooWrong', 'oooo');
    }

    private function createMetable(array $attributes = []): SampleMetableTypes
    {
        return factory(SampleMetableTypes::class)->create($attributes);
    }
}
