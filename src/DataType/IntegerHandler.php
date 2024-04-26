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

    public function getNumericValue(mixed $value): null|int|float
    {
        return $value;
    }
}
