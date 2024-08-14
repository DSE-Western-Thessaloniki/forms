<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('username', 'admin0')->first();

        if (! $user) {
            throw new \Exception('User admin0 not found!');
        }

        $forms = Form::factory()
            ->count(40)
            ->for($user)
            ->has(
                FormField::factory()
                    ->count(15)
                    ->state(new Sequence(function ($sequence) {
                        $type = rand(0, 10);

                        // Αν ο τύπος του πεδίου χρειάζεται επιπλέον επιλογές
                        if (in_array($type, [2, 3, 4])) {
                            $listvalues = [];
                            $times = rand(1, 10);
                            for ($i = 0; $i < $times; $i++) {
                                array_push(
                                    $listvalues,
                                    [
                                        'id' => $i,
                                        'value' => $this->faker->word(),
                                    ]
                                );
                            }

                            return [
                                'sort_id' => $sequence->index,
                                'type' => $type,
                                'listvalues' => json_encode($listvalues),
                            ];
                        } else {
                            return [
                                'sort_id' => $sequence->index,
                                'type' => $type,
                                'listvalues' => '',
                            ];
                        }
                    })),
                'form_fields'
            )
            ->create();

        // Σύνδεση φόρμας με σχολική μονάδα και κατηγορία σχολείου
        foreach ($forms as $form) {
            $form->schools()->attach(School::inRandomOrder()->first()->id);
            $form->school_categories()->attach(SchoolCategory::inRandomOrder()->first()->id);
        }
    }
}
