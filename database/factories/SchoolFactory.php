<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SchoolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = School::class;

    public const gymnasio = 0;
    public const gel = 1;
    public const epal = 2;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => Str::random(8),
            'code' => Str::random(9),
            'email' => $this->faker->email(),
            'active' => 1,
        ];
    }

    /**
     * Δημιουργία σχολείου συγκεκριμένης κατηγορίας
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function category($type = SchoolFactory::gymnasio)
    {
        switch($type) {
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
    }


}
