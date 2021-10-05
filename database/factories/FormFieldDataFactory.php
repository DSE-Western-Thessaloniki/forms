<?php

namespace Database\Factories;

use App\Models\FormFieldData;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFieldDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormFieldData::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'data' => '',
            'record' => 0,
        ];
    }
}
