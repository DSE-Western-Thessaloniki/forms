<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SelectionList>
 */
class SelectionListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $data = [];
        for ($i = 0; $i < $this->faker->randomDigitNotZero(); $i++) {
            $data[] = [
                'id' => $i,
                'value' => $this->faker->word()
            ];
        }

        return [
            'name' => $this->faker->sentence(3),
            'active' => $this->faker->boolean(),
            'data' => $data
        ];
    }
}
