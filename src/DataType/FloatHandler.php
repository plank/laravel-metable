<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of floats.
 *
 * @author Sean Fraser <sean@plankdesign.com>
 */
class FloatHandler extends ScalarHandler
{
    /**
     * {@InheritDoc}
     */
    protected $type = 'double';

    /**
     * {@InheritDoc}
     */
    public function getDataType() : string
    {
        return 'float';
    }
}
