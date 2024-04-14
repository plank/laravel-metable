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
use Plank\Metable\DataType\SerializeHandler;
use Plank\Metable\DataType\StringHandler;
use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\Mocks\SampleSerializable;
use Plank\Metable\Tests\TestCase;
use stdClass;

class HandlerTest extends TestCase
{
    private static $resource;
    static public function handlerProvider(): array
    {
        $timestamp = '2017-01-01 00:00:00.000000+0000';
        $datetime = Carbon::createFromFormat('Y-m-d H:i:s.uO', $timestamp);

        $object = new stdClass();
        $object->foo = 'bar';
        $object->baz = 3;

        self::$resource = fopen('php://memory', 'r');

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
            'serialize' => [
                new SerializeHandler(),
                'serialized',
                ['foo' => 'bar', 'baz' => [3]],
                [self::$resource],
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

    public static function tearDownAfterClass(): void
    {
        if (self::$resource) {
            fclose(self::$resource);
            self::$resource = null;
        }
        parent::tearDownAfterClass();
    }

    /**
     * @dataProvider handlerProvider
     */
    public function test_it_can_verify_and_serialize_data(
        HandlerInterface $handler,
        string $type,
        mixed $value,
        array $incompatible
    ): void {
        $this->assertEquals($type, $handler->getDataType());
        $this->assertTrue($handler->canHandleValue($value));

        foreach ($incompatible as $incompatibleValue) {
            $this->assertFalse($handler->canHandleValue($incompatibleValue));
        }

        $serialized = $handler->serializeValue($value);
        $unserialized = $handler->unserializeValue($serialized);

        $this->assertEquals($value, $unserialized);
    }
}
