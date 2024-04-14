<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of strings.
 */
class StringHandler extends ScalarHandler
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'string';

    public function getNumericValue(mixed $value, string $serializedValue): null|int|float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        return null;
    }

    public function getStringValue(mixed $value, string $serializedValue): null|string
    {
        return substr($value, 0, 255);
    }
}
