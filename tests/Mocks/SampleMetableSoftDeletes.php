<?php

namespace Plank\Metable\Tests\Mocks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Metable\Metable;
use Plank\Metable\MetableInterface;

class SampleMetableSoftDeletes extends Model implements MetableInterface
{
    use Metable;
    use SoftDeletes;

    protected $table = 'sample_metables';
}
