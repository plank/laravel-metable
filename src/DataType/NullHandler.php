<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of null values.
 *
 * @author Sean Fraser <sean@plankdesign.com>
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
    public function getDataType() : string
    {
        return 'null';
    }
}
