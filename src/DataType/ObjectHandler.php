<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of plain objects.
 * @deprecated Use SignedSerializeHandler instead.
 */
class ObjectHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'object';
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue(mixed $value): bool
    {
        return is_object($value);
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value): string
    {
        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $serializedValue): mixed
    {
        return json_decode($serializedValue, false);
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
