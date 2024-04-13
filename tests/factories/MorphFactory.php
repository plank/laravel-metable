<?php

use Plank\Metable\Tests\Mocks\SampleMorph;

$factory = app(Illuminate\Database\Eloquent\Factory::class);
$factory->define(SampleMorph::class, function (Faker\Generator $faker) {
    return [];
});
