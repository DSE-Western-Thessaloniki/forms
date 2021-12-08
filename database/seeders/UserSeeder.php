<?php

namespace Database\Seeders;

use App\Models\Role;
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
        $admin_role = Role::firstWhere('name', '=', 'Administrator');
        $author_role = Role::firstWhere('name', '=', 'Author');
        $user_role = Role::firstWhere('name', '=', 'User');
        if (!($admin_role && $author_role && $user_role)) {
            throw new \RuntimeException("Πρέπει πρώτα να δημιουργηθούν οι ρόλοι και μετά οι χρήστες");
        }

        // Admins
        User::factory()
            ->count(5)
            ->state(new Sequence(fn ($sequence) => ['username' => 'admin'.$sequence->index]))
            ->hasAttached($admin_role)
            ->create();

        // Authors
        User::factory()
            ->count(10)
            ->hasAttached($author_role)
            ->create();

        // Users
        User::factory()
            ->count(10)
            ->hasAttached($user_role)
            ->create();
    }
}
