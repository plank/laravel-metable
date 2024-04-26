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
}
