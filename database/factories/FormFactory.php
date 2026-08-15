<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'notes' => $this->faker->text(),
            'active' => $this->faker->boolean(),
            'multiple' => $this->faker->boolean(),
            'for_teachers' => 0,
            'for_all_teachers' => 0,
        ];
    }
}
