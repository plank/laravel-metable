<?php

use Plank\Metable\Metable;

class SampleMorph extends SampleMetable
{
    use Metable;

    protected $table = 'sample_metables';

    public function getMorphClass()
    {
        return SampleMetable::class;
    }
}
