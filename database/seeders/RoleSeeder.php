<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['name' => 'Administrator'],
            ['name' => 'Author'],
            ['name' => 'User'],
        ];

        foreach($roles as $role) {
            Role::updateOrCreate($role);
        }
    }
}
