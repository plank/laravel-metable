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
    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof Model;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value): string
    {
        if ($value->exists) {
            return get_class($value) . '#' . $value->getKey();
        }

        return get_class($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $serializedValue): mixed
    {
        // Return blank instances.
        if (strpos($serializedValue, '#') === false) {
            return new $serializedValue();
        }

        // Fetch specific instances.
        list($class, $id) = explode('#', $serializedValue);

        return with(new $class())->findOrFail($id);
    }
}
