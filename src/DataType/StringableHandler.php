<?php

namespace Plank\Metable\DataType;

use Illuminate\Support\Stringable;

class StringableHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'stringable';
    }

    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof Stringable;
    }

    public function serializeValue(mixed $value): string
    {
        return (string) $value;
    }

    public function getNumericValue(mixed $value): null|int|float
    {
        return is_numeric((string)$value) ? (float)(string)$value : null;
    }

    public function getStringValue(mixed $value): null|string
    {
        return substr(
            (string)$value,
            0,
            config('metable.stringValueIndexLength', 255)
        );
    }

    public function unserializeValue(string $serializedValue): mixed
    {
        return new Stringable($serializedValue);
    }

    public function isIdempotent(): bool
    {
        return true;
    }

    public function useHmacVerification(): bool
    {
        return false;
    }
}
