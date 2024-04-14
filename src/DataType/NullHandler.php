<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of null values.
 */
class NullHandler extends ScalarHandler
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'NULL';

    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'null';
    }

    public function getNumericValue(mixed $value, string $serializedValue): null|int|float
    {
        return null;
    }

    public function getStringValue(mixed $value, string $serializedValue): null|string
    {
        return null;
    }
}
