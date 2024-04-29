<?php

use Plank\Metable\Meta;

$factory = app(Illuminate\Database\Eloquent\Factory::class);
$factory->define(Meta::class, function (Faker\Generator $faker) {
    return [];
});
