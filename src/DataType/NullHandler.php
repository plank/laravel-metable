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
    protected $type = 'NULL';

    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'null';
    }
}
