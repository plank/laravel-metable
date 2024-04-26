<?php

namespace Plank\Metable\DataType;

use Serializable;

/**
 * Handle serialization of Serializable objects.
 * @deprecated Use SignedSerializeHandler instead.
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
        $allowedClasses = config('metable.serializableHandlerAllowedClasses', false);
        return unserialize($serializedValue, ['allowed_classes' => $allowedClasses]);
    }

    public function getNumericValue(mixed $value): null|int|float
    {
        return null;
    }

    public function useHmacVerification(): bool
    {
        return false;
    }
}
