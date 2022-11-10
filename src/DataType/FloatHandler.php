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
    protected function getType(): string
    {
        return 'double';
    }
}
