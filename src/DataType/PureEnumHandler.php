<?php

namespace Plank\Metable\DataType;

class PureEnumHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'enum';
    }

    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof \UnitEnum && !($value instanceof \BackedEnum);
    }

    public function serializeValue(mixed $value): string
    {
        return sprintf(
            '%s#%s',
            $value::class,
            $value->name
        );
    }

    public function unserializeValue(string $serializedValue): mixed
    {
        [$class, $name] = explode('#', $serializedValue, 2);

        if (!class_exists($class)
            || !is_a($class, \UnitEnum::class, true)
        ) {
            return null;
        }

        return constant("$class::$name");
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
