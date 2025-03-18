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
        if (is_numeric($value) && preg_match('/^-?(\d{1,20}(\.\d{1,16})?)$/', $value)) {
            return (float)$value;
        }
        return null;
    }
}
