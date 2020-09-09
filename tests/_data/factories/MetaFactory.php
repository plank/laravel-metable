<?php

use Illuminate\Database\Eloquent\Factories\Factory;

class MetaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Plank\Metable\Meta::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }
}
