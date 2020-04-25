<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Populate levels table
        DB::table('levels')->insert([
            ['level' => 0, 'name' => 'admin'], 
            ['level' => 10, 'name' => 'editor'], 
            ['level' => 50, 'name' => 'moderator'], 
            ['level' => 100, 'name' => 'user']
        ]);
    }
}
