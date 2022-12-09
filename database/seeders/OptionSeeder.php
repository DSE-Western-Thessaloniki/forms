<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ο πρώτος πίνακας είναι το κλειδί για την επιλογή και ο δεύτερος η προεπιλεγμένη τιμή
        $options = [
            [
                ['name' => 'first_run'], ['value' => '1'],
            ],
            [
                ['name' => 'allow_teacher_login'], ['value' => '1'],
            ],
            [
                ['name' => 'allow_all_teachers'], ['value' => '1'],
            ],
        ];

        foreach($options as $option) {
            Option::updateOrCreate($option[0], $option[1]);
        }
    }
}
