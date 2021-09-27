<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admins
        User::factory()
            ->count(5)
            ->state(new Sequence(fn ($sequence) => ['username' => 'admin'.$sequence->index]))
            ->hasRoles(1, ['name' => 'Administrator'])
            ->create();

        // Users
        User::factory()
            ->count(20)
            ->hasRoles(1, new Sequence(['name' => 'Author'], ['name' => 'User']))
            ->create();
    }
}
