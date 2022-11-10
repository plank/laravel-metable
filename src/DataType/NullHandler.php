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
    public function getType(): string
    {
        return 'NULL';
    }
}
