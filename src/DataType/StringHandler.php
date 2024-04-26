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

    public function getNumericValue(mixed $value): null|int|float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        return null;
    }
}
