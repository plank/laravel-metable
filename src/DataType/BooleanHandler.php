<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of booleans.
 */
class BooleanHandler extends ScalarHandler
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'boolean';

    public function getNumericValue(mixed $value): null|int|float
    {
        return $value ? 1 : 0;
    }

    public function getStringValue(mixed $value): null|string
    {
        return $value ? 'true' : 'false';
    }
}
