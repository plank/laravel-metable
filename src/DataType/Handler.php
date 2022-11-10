<?php

namespace Plank\Metable\DataType;

use Illuminate\Support\Str;

abstract class Handler implements HandlerInterface
{
    public function getDataType(): string
    {
        return Str::of(static::class)
            ->classBasename()
            ->before('Handler')
            ->lower()
            ->__toString();
    }
}
