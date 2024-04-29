<?php

namespace Plank\Metable\DataType;

/**
 * Securely handle any type of value using php serialize with encryption.
 */
final class SignedSerializeHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'serialized';
    }

    public function canHandleValue(mixed $value): bool
    {
        return !is_resource($value);
    }

    public function serializeValue(mixed $value): string
    {
        return serialize($value);
    }

    public function unserializeValue(string $serializedValue): mixed
    {
        return unserialize(
            $serializedValue,
            [
                'allowed_classes' => config(
                    'metable.signedSerializeHandlerAllowedClasses',
                    true
                )
            ]
        );
    }

    public function getNumericValue(mixed $value): null|int|float
    {
        return null;
    }

    public function useHmacVerification(): bool
    {
        return true;
    }
}
