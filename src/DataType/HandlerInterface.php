<?php

namespace Plank\Metable\DataType;

/**
 * Provides means to serialize and unserialize values of different data types.
 */
interface HandlerInterface
{
    /**
     * Return the identifier for the data type being handled.
     *
     * @return string
     */
    public function getDataType(): string;

    /**
     * Determine if the value is of the correct type for this handler.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function canHandleValue(mixed $value): bool;

    /**
     * Convert the value to a string, so that it can be stored in the database.
     *
     * @param mixed $value
     *
     * @return string
     */
    public function serializeValue(mixed $value): string;

    public function getNumericValue(mixed $value): null|int|float;

    /**
     * Convert a serialized string back to its original value.
     *
     * @param string $serializedValue
     *
     * @return mixed
     */
    public function unserializeValue(string $serializedValue): mixed;

    public function useHmacVerification(): bool;
}
