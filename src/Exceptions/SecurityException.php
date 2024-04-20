<?php

namespace Plank\Metable\Exceptions;

class SecurityException extends \RuntimeException
{
    public static function hmacVerificationFailed(): self
    {
        return new static('Cannot unserialize Meta: HMAC verification failed.');
    }
}
