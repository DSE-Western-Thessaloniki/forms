<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $gender = rand(0, 1) === 0 ? 'male' : 'female';
        return [
            'name' => $this->faker->firstName($gender),
            'surname' => $this->faker->lastName($gender),
            'am' => $this->faker->numerify('######'),
            'afm' => $this->faker->numerify('#########'),
        ];
    }
}
