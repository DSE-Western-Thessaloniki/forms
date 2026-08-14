<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcceptedFiletype;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcceptedFiletype>
 */
class AcceptedFiletypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => $this->faker->words(3, true),
            'extension' => '.'.$this->faker->fileExtension(),
        ];
    }
}
