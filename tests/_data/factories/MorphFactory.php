<?php

use Illuminate\Database\Eloquent\Factories\Factory;

class MorphFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SampleMorph::class;

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
