<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of plain objects.
 *
 * @author Sean Fraser <sean@plankdesign.com>
 */
class ObjectHandler implements Handler
{
    /**
     * {@InheritDoc}
     */
    public function getDataType() : string
    {
        return 'object';
    }

    /**
     * {@InheritDoc}
     */
    public function canHandleValue($value) : bool
    {
        return is_object($value);
    }

    /**
     * {@InheritDoc}
     */
    public function serializeValue($value) : string
    {
        return json_encode($value);
    }

    /**
     * {@InheritDoc}
     */
    public function unserializeValue(string $value)
    {
        return json_decode($value, false);
    }
}
