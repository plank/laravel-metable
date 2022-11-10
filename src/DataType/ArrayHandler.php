<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of arrays.
 */
class ArrayHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    public function canHandleValue($value): bool
    {
        return is_array($value);
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue($value): string
    {
        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $value)
    {
        return json_decode($value, true);
    }
}
