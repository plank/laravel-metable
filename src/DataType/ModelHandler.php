<?php

namespace Plank\Metable\DataType;

use Illuminate\Database\Eloquent\Model;

/**
 * Handle serialization of Eloquent Models.
 *
 * @author Sean Fraser <sean@plankdesign.com>
 */
class ModelHandler implements Handler
{
    /**
     * {@InheritDoc}
     */
    public function getDataType() : string
    {
        return 'model';
    }

    /**
     * {@InheritDoc}
     */
    public function canHandleValue($value) : bool
    {
        return $value instanceof Model;
    }

    /**
     * {@InheritDoc}
     */
    public function serializeValue($value) : string
    {
        if ($value->exists) {
            return get_class($value).'#'.$value->getKey();
        }
        return get_class($value);
    }

    /**
     * {@InheritDoc}
     */
    public function unserializeValue(string $value)
    {
        // Return blank instances.
        if (strpos($value, '#') === false) {
            return new $value;
        }

        // Fetch specific instances.
        list($class, $id) = explode('#', $value);
        return with(new $class)->findOrFail($id);
    }
}
