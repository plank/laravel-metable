<?php

namespace Plank\Metable\DataType;

/**
 * Handle serialization of arrays.
 * @deprecated Use SignedSerializeHandler instead.
 */
class ArrayHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDataType(): string
    {
        return 'array';
    }

    /**
     * {@inheritdoc}
     */
    public function canHandleValue(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function unserializeValue(string $serializedValue): mixed
    {
        return json_decode(
            $serializedValue,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
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
