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

    public function unserializeValue(string $serializedValue): mixed
    {
        return new Stringable($serializedValue);
    }

    public function useHmacVerification(): bool
    {
        return false;
    }
}
