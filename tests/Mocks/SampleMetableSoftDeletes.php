<?php

namespace Plank\Metable\Tests\Mocks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Metable\Metable;

class SampleMetableSoftDeletes extends Model
{
    use Metable;
    use SoftDeletes;

    protected $table = 'sample_metables';
}
