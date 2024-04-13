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
    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof Serializable;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value): string
    {
        return serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $serializedValue): mixed
    {
        return unserialize($serializedValue);
    }
}
