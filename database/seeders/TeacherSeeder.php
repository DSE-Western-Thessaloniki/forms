<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Teacher::factory()
            ->count(20)
            ->create();

        $testTeacher = Teacher::factory()->create();
        $testTeacher->update([
            "am" => "111111",
            "afm" => "000111000"
        ]);
    }
}
