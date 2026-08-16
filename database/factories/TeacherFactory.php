<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = random_int(0, 1) === 0 ? 'male' : 'female';

        return [
            'name' => $this->faker->firstName($gender),
            'surname' => $this->faker->lastName($gender),
            'am' => $this->faker->numerify('######'),
            'afm' => $this->faker->numerify('#########'),
        ];
    }
}
