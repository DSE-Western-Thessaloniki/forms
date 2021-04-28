<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = [
            ['level' => 0, 'name' => 'admin'],
            ['level' => 10, 'name' => 'editor'],
            ['level' => 50, 'name' => 'moderator'],
            ['level' => 100, 'name' => 'user']
        ];

        // Populate levels table
        foreach($levels as $level) {
            Level::updateOrCreate($level);
        }
    }
}
