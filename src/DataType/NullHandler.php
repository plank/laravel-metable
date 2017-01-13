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
     * {@InheritDoc}
     */
    protected $type = 'NULL';

    /**
     * {@InheritDoc}
     */
    public function getDataType() : string
    {
        return 'null';
    }
}
