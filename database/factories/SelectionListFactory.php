<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SelectionList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SelectionList>
 */
class SelectionListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $data = [];
        for ($i = 0; $i < $this->faker->randomDigitNotZero(); $i++) {
            $data[] = [
                'id' => $i,
                'value' => $this->faker->word(),
            ];
        }

        return [
            'name' => $this->faker->sentence(3),
            'active' => $this->faker->boolean(),
            'data' => $data,
        ];
    }
}
