<?php

namespace Plank\Metable\Tests\Mocks;

use Illuminate\Database\Eloquent\Model;
use Plank\Metable\Metable;
use Plank\Metable\MetableInterface;

class SampleMetable extends Model implements MetableInterface
{
    use Metable;

    protected $defaultMetaValues = [
        'foo' => 'bar'
    ];

    protected $castsMeta = [
        'castable' => 'string',
    ];
}
