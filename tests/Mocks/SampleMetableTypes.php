<?php

namespace Plank\Metable\Tests\Mocks;

use Illuminate\Database\Eloquent\Model;
use Plank\Metable\DataType\BooleanHandler;
use Plank\Metable\DataType\StringHandler;
use Plank\Metable\Metable;

class SampleMetableTypes extends Model
{
    use Metable;

    protected $table = 'sample_metables';

    protected $metaCasts = [
        'fooBool' => BooleanHandler::class,
        'fooString' => StringHandler::class,
        'fooWrong' => SampleMetable::class
    ];
}
