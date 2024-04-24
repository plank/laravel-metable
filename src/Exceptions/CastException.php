<?php

namespace Plank\Metable\Exceptions;

class CastException extends \InvalidArgumentException
{
    public static function invalidClassCast(string $className, mixed $value): self
    {
        return new static(
            sprintf(
                "Cannot cast value to class: value of type %s is not an instance of %s",
                get_debug_type($value),
                $className
            )
        );
    }
}
