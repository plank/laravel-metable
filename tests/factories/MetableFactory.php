<?php

use Plank\Metable\Tests\Mocks\SampleMetable;
use Plank\Metable\Tests\Mocks\SampleMetableSoftDeletes;

$factory = app(Illuminate\Database\Eloquent\Factory::class);

$factory->define(SampleMetable::class, function (Faker\Generator $faker) {
    return [];
});

$factory->define(SampleMetableSoftDeletes::class, function (Faker\Generator $faker) {
    return [];
});
