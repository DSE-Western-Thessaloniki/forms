<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SchoolCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolCategory>
 */
class SchoolCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SchoolCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
