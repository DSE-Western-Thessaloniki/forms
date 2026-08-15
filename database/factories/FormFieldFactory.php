<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'type' => 0,
            'sort_id' => 0,
            'listvalues' => '',
            'required' => $this->faker->boolean(),
        ];
    }
}
