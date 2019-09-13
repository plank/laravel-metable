<?php

namespace Plank\Metable\Exceptions;

use Exception;

/**
 * Data Type registry exception.
 */
class DataTypeException extends Exception
{
    public static function handlerNotFound(string $type): self
    {
        return new static("Meta handler not found for type identifier '{$type}'");
    }

    public static function handlerNotFoundForValue($value): self
    {
        $type = is_object($value) ? get_class($value) : gettype($value);

        return new static("Meta handler not found for value of type '{$type}'");
    }
}
