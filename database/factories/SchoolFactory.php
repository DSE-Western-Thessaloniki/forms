<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    public const gymnasio = 0;

    public const gel = 1;

    public const epal = 2;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'username' => Str::random(8),
            'code' => $this->faker->unique()->numerify('#######'),
            'email' => $this->faker->unique()->email(),
            'active' => 1,
            'telephone' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * Δημιουργία σχολείου συγκεκριμένης κατηγορίας
     */
    public function category($type = SchoolFactory::gymnasio): static
    {
        switch ($type) {
            case SchoolFactory::gymnasio:
                return $this->state(function (array $attributes) {
                    return [
                        'name' => ($this->faker->randomDigit() + 1).'ο ΓΥΜΝΑΣΙΟ '.$this->faker->prefecture(),
                    ];
                });
                break;
            case SchoolFactory::gel:
                return $this->state(function (array $attributes) {
                    return [
                        'name' => ($this->faker->randomDigit() + 1).'ο ΓΕΛ '.$this->faker->prefecture(),
                    ];
                });
                break;
            case SchoolFactory::epal:
                return $this->state(function (array $attributes) {
                    return [
                        'name' => ($this->faker->randomDigit() + 1).'ο ΕΠΑΛ '.$this->faker->prefecture(),
                    ];
                });
                break;
        }

        throw new \Exception("Invalid school type '$type'");
    }
}
