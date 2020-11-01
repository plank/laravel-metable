<?php

namespace Plank\Metable\Tests\Mocks;

use Illuminate\Database\Eloquent\Model;
use Plank\Metable\Metable;

class SampleMetable extends Model
{
    use Metable;

    protected $defaultMetaValues = [
        'foo' => 'bar'
    ];
}
