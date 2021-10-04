<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('username', 'admin0')->first();

        Form::factory()
            ->count(40)
            ->for($user)
            ->has(
                FormField::factory()
                    ->count(rand(1, 20))
                    ->state(new Sequence(function($sequence) {
                        $type = rand(0,10);

                        // Δεν χρησιμοποιείται ο τύπος για την επιλογή αρχείου
                        if ($type == 5) {
                            $type = 0;
                        }
                        // Αν ο τύπος του πεδίου χρειάζεται επιπλέον επιλογές
                        if (in_array($type, [2, 3, 4])) {
                            $listvalues = array();
                            for ($i = 0; $i < rand(1, 10); $i++) {
                                array_push(
                                    $listvalues,
                                    [
                                        "id" => $i,
                                        "value" => Str::random(),
                                    ]
                                );
                            }

                            return [
                                'sort_id' => $sequence->index,
                                'type' => $type,
                                'listvalues' => json_encode($listvalues)
                            ];
                        }
                        else {
                            return [
                                'sort_id' => $sequence->index,
                                'type' => $type,
                                'listvalues' => ''
                            ];
                        }
                    })),
                'form_fields'
            )
            ->create();
    }
}
