<?php

namespace Plank\Metable\DataType;

class BackedEnumHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'backed_enum';
    }

    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof \BackedEnum;
    }

    public function serializeValue(mixed $value): string
    {
        return sprintf(
            '%s#%s',
            $value::class,
            $value->value
        );
    }

    public function unserializeValue(string $serializedValue): mixed
    {
        [$class, $value] = explode('#', $serializedValue, 2);

        if (!class_exists($class)
            || !is_a($class, \BackedEnum::class, true)
        ) {
            return null;
        }

        return $class::tryFrom($value);
    }

    public function getNumericValue(mixed $value): null|int|float
    {
        if (is_numeric($value->value)) {
            return $value->value;
        }
        return null;
    }

    public function useHmacVerification(): bool
    {
        return false;
    }
}
