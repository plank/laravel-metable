<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of floats.
 */
class FloatHandler extends ScalarHandler
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'double';

    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'float';
    }

    public function getNumericValue(mixed $value): null|int|float
    {
        return $value;
    }
}
