<?php

namespace Plank\Metable\DataType;

/**
 * Securely handle any type of value using php serialize with encryption.
 */
class SerializeHandler implements HandlerInterface
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
        return app('encrypter')->encrypt($value, true);
    }

    public function unserializeValue(string $serializedValue): mixed
    {
        return app('encrypter')->decrypt($serializedValue, true);
    }

    public function getNumericValue(mixed $value, string $serializedValue): null|int|float
    {
        return null;
    }

    public function getStringValue(mixed $value, string $serializedValue): null|string
    {
        return null;
    }
}