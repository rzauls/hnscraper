<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected function withFaker()
    {
        return \Faker\Factory::create('en');
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->numberBetween(),
            'title' => $this->faker->title(),
            'author' => $this->faker->name(),
            'points' => $this->faker->numberBetween(0, 200),
            'link' => $this->faker->url(),
            // 'created_at' => now(),
            // 'updated_at' => now(),
        ];
    }
}
