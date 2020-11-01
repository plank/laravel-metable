<?php

namespace Plank\Metable\Tests\Integration\DataType;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Plank\Metable\DataType\ArrayHandler;
use Plank\Metable\DataType\BooleanHandler;
use Plank\Metable\DataType\DateTimeHandler;
use Plank\Metable\DataType\FloatHandler;
use Plank\Metable\DataType\HandlerInterface;
use Plank\Metable\DataType\IntegerHandler;
use Plank\Metable\DataType\ModelCollectionHandler;
use Plank\Metable\DataType\ModelHandler;
use Plank\Metable\DataType\NullHandler;
use Plank\Metable\DataType\ObjectHandler;
use Plank\Metable\DataType\SerializableHandler;
use Plank\Metable\DataType\StringHandler;
use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\Mocks\SampleSerializable;
use Plank\Metable\Tests\TestCase;
use stdClass;

class HandlerTest extends TestCase
{
    public function handlerProvider()
    {
        $timestamp = '2017-01-01 00:00:00.000000+0000';
        $datetime = Carbon::createFromFormat('Y-m-d H:i:s.uO', $timestamp);

        $object = new stdClass();
        $object->foo = 'bar';
        $object->baz = 3;

        return [
            'array' => [
                new ArrayHandler(),
                'array',
                ['foo' => ['bar'], 'baz'],
                [new stdClass()],
            ],
            'boolean' => [
                new BooleanHandler(),
                'boolean',
                true,
                [1, 0, '', [], null],
            ],
            'datetime' => [
                new DateTimeHandler(),
                'datetime',
                $datetime,
                [2017, '2017-01-01'],
            ],
            'float' => [
                new FloatHandler(),
                'float',
                1.1,
                ['1.1', 1],
            ],
            'integer' => [
                new IntegerHandler(),
                'integer',
                3,
                [1.1, '1'],
            ],
            'model' => [
                new ModelHandler(),
                'model',
                new SampleMetable(),
                [new stdClass()],
            ],
            'model collection' => [
                new ModelCollectionHandler(),
                'collection',
                new Collection([new SampleMetable()]),
                [collect()],
            ],
            'null' => [
                new NullHandler(),
                'null',
                null,
                [0, '', 'null', [], false],
            ],
            'object' => [
                new ObjectHandler(),
                'object',
                $object,
                [[]],
            ],
            'serializable' => [
                new SerializableHandler(),
                'serializable',
                new SampleSerializable(['foo' => 'bar']),
                [],
            ],
            'string' => [
                new StringHandler(),
                'string',
                'foo',
                [1, 1.1],
            ],
        ];
    }

    /**
     * @dataProvider handlerProvider
     */
    public function test_it_specifies_a_datatype_identifier(HandlerInterface $handler, $type)
    {
        $this->assertEquals($type, $handler->getDataType());
    }

    /**
     * @dataProvider handlerProvider
     */
    public function test_it_can_verify_compatibility(HandlerInterface $handler, $type, $value, $incompatible)
    {
        $this->assertTrue($handler->canHandleValue($value));

        foreach ($incompatible as $value) {
            $this->assertFalse($handler->canHandleValue($value));
        }
    }

    /**
     * @dataProvider handlerProvider
     */
    public function test_it_can_serialize_and_unserialize_values(HandlerInterface $handler, $type, $value)
    {
        $serialized = $handler->serializeValue($value);
        $unserialized = $handler->unserializeValue($serialized);

        $this->assertEquals($value, $unserialized);
    }
}
