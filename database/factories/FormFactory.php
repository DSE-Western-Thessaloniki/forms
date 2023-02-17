<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
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
