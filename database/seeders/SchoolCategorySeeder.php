<?php

namespace Database\Seeders;

use App\Models\SchoolCategory;
use Illuminate\Database\Seeder;

class SchoolCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'ΓΕΛ'],
            ['name' => 'ΓΥΜΝΑΣΙΑ'],
            ['name' => 'ΕΠΑΛ'],
            ['name' => 'ΣΜΕΑΕ']
        ];

        foreach($categories as $category) {
            SchoolCategory::updateOrCreate($category);
        }
    }
}
