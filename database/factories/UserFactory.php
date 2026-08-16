<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
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

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'username' => $this->faker->username(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'active' => random_int(0, 1),
            'updated_by' => 0,
            'password_reset' => 0,
        ];
    }

    /**
     * Indicate that the user is suspended.
     */
    public function suspended(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => 0,
            ];
        });
    }

    /**
     * Indicate that the user is an administrator.
     */
    public function admin(): static
    {
        return $this->hasAttached(
            Role::factory()->state(['name' => 'Administrator'])->count(1)
        );
    }

    /**
     * Indicate that the user is an author.
     */
    public function author(): static
    {
        return $this->hasAttached(
            Role::factory()->state(['name' => 'Author'])->count(1)
        );
    }

    /**
     * Indicate that the user is a user.
     */
    public function user(): static
    {
        return $this->hasAttached(
            Role::factory()->state(['name' => 'User'])->count(1)
        );
    }
}
