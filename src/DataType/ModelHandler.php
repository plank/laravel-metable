<?php

namespace Plank\Metable\DataType;

use Illuminate\Database\Eloquent\Model;

/**
 * Handle serialization of Eloquent Models.
 */
class ModelHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'model';
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue($value): bool
    {
        return $value instanceof Model;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue($value): string
    {
        if ($value->exists) {
            return get_class($value) . '#' . $value->getKey();
        }

        return get_class($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $value)
    {
        // Return blank instances.
        if (strpos($value, '#') === false) {
            return new $value();
        }

        // Fetch specific instances.
        list($class, $id) = explode('#', $value);

        return with(new $class())->findOrFail($id);
    }
}
