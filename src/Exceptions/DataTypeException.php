<?php

namespace Plank\Metable\Exceptions;

use Exception;

class DataTypeException extends Exception
{

    public static function handlerNotFound(string $type)
    {
        return new static("Meta handler not found for datatype '{$type}'");
    }
}
