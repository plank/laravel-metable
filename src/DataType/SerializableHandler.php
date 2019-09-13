<?php

namespace Plank\Metable\DataType;

use Serializable;

/**
 * Handle serialization of Serializable objects.
 */
class SerializableHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'serializable';
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue($value): bool
    {
        return $value instanceof Serializable;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue($value): string
    {
        return serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $value)
    {
        return unserialize($value);
    }
}
