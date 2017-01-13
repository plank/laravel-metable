<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of scalar values.
 *
 * @author Sean Fraser <sean@plankdesign.com>
 */
abstract class ScalarHandler implements Handler
{
    /**
     * The name of the scalar data type.
     * @var string
     */
    protected $type;

    /**
     * {@InheritDoc}
     */
    public function getDataType() : string
    {
        return $this->type;
    }

    /**
     * {@InheritDoc}
     */
    public function canHandleValue($value) : bool
    {
        return gettype($value) == $this->type;
    }

    /**
     * {@InheritDoc}
     */
    public function serializeValue($value) : string
    {
        settype($value, 'string');
        return $value;
    }

    /**
     * {@InheritDoc}
     */
    public function unserializeValue(string $value)
    {
        settype($value, $this->type);
        return $value;
    }
}
