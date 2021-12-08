<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'username' => $this->faker->username(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'active' => rand(0, 1),
            'updated_by' => 0,
        ];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => 0,
            ];
        });
    }

    /**
     * Indicate that the user is an administrator.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function admin()
    {
        return $this->hasAttached(
            Role::factory()->state(['name' => 'Administrator'])->count(1)
        );
    }

    /**
     * Indicate that the user is an author.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function author()
    {
        return $this->hasAttached(
            Role::factory()->state(['name' => 'Author'])->count(1)
        );
    }

    /**
     * Indicate that the user is a user.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function user()
    {
        return $this->hasAttached(
            Role::factory()->state(['name' => 'User'])->count(1)
        );
    }
}
