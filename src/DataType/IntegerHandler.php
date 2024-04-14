<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of integers.
 */
class IntegerHandler extends ScalarHandler
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'integer';

    public function getNumericValue(mixed $value, string $serializedValue): null|int|float
    {
        return $value;
    }

    public function getStringValue(mixed $value, string $serializedValue): null|string
    {
        return (string) $value;
    }
}
