<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FormFieldData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormFieldData>
 */
class FormFieldDataFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'data' => '',
            'record' => 0,
        ];
    }
}
