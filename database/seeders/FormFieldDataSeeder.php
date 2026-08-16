<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldData;
use App\Models\School;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Random\Randomizer;

class FormFieldDataSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var Generator
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
     * @return Generator
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
        $forms = Form::with(['form_fields', 'schools', 'school_categories'])->get();
        foreach ($forms as $form) {
            foreach ($form->form_fields as $field) {
                // Κάθε σχολείο πρέπει να συμπληρώσει την φόρμα
                foreach ($form->schools as $school) {
                    $this->create_field_data($field, $school, 1);
                }

            }

            // Κάποια σχολεία της κατηγορίας πρέπει επίσης να δώσουν στοιχεία
            foreach ($form->school_categories as $category) {
                $count = 1;
                if ($form->multiple) {
                    $count = random_int(1, 10);
                }
                $r = new Randomizer;
                $keys = $r->pickArrayKeys($category->schools->toArray(), random_int(1, count($category->schools)));
                if (! is_array($keys)) {
                    $keys = [$keys];
                }
                foreach ($form->form_fields as $field) {
                    foreach ($keys as $key) {
                        if (! $form->schools->contains($category->schools[$key])) {
                            $this->create_field_data($field, $category->schools[$key], $count);
                        }
                    }
                }
            }
        }
    }

    protected function create_field_data(FormField $field, School $school, int $count): void
    {
        FormFieldData::factory()
            ->count($count)
            ->state(new Sequence(function ($sequence) use ($field) {
                switch ($field->type) {
                    case 0: // Πεδίο κειμένου
                        return [
                            'data' => $this->faker->sentence(),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 1: // Περιοχή κειμένου
                        return [
                            'data' => $this->faker->text(),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 2: // Επιλογή ενός από πολλά
                    case 4: // Λίστα επιλογών
                        $list = json_decode($field->listvalues);
                        $item = random_int(0, count($list) - 1);

                        return [
                            'data' => $list[$item]->id,
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 3: // Πολλαπλή επιλογή
                        $list = json_decode($field->listvalues);
                        $itemcount = random_int(1, count($list));
                        $r = new Randomizer;
                        $keys = $r->pickArrayKeys($list, $itemcount);
                        $data = [];
                        if (is_array($keys)) {
                            foreach ($keys as $key) {
                                array_push($data, $list[$key]->id);
                            }
                        } else {
                            array_push($data, $list[$keys]->id);
                        }
                        $data = json_encode($data);

                        return [
                            'data' => $data,
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 5: // Αρχείο
                        return [
                            'data' => $this->faker->word().'.'.$this->faker->fileExtension(),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 6: // Ημερομηνία
                        return [
                            'data' => $this->faker->date(),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 7: // Αριθμός
                        return [
                            'data' => $this->faker->randomNumber(),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 8: // Τηλέφωνο
                        return [
                            'data' => $this->faker->numerify('##########'),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 9: // E-mail
                        return [
                            'data' => $this->faker->email(),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                    case 10: // Url
                        return [
                            'data' => $this->faker->url(),
                            'record' => $sequence->index,
                        ];
                        break; // @phpstan-ignore-line
                }
            }))
            ->for($school, 'school')
            ->for($field, 'form_field')
            ->create();
    }
}
