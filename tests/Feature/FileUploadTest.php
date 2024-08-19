<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\Option;
use App\Models\OtherTeacher;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Tests\TestCasManager;

beforeEach(function () {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();

    $this->app->singleton('cas', function () {
        return new TestCasManager();
    });

    test_cas_null();
});

it('can upload a file on a report as user logged in through cas (school) (no multiple)', function () {

    test_cas_logged_in();

    Storage::fake('local');

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(1)
                ->state([
                    'sort_id' => 1,
                    'type' => 5,
                    'listvalues' => '',
                    'options' => '{"filetype":{"value":"-1","custom_value":".doc"}}',
                ]),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = UploadedFile::fake()->create('test.doc');
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put(route('report.update', $form->id), $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $this->assertDatabaseHas('form_field_data', [
        'school_id' => $school->id,
        'form_field_id' => $fields[0]->id,
        'data' => 'test.doc',
    ]);

    expect(Storage::exists("report/{$form->id}/school/{$school->id}/0/{$fields[0]->id}"))->toBeTrue();
});

it('can upload a file on a report as user logged in through cas (school) (multiple)', function () {

    test_cas_logged_in();

    Storage::fake('local');

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(1)
                ->state([
                    'sort_id' => 1,
                    'type' => 5,
                    'listvalues' => '',
                    'options' => '{"filetype":{"value":"-1","custom_value":".doc,.xls"}}',
                ]),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = UploadedFile::fake()->create('test.doc');
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put(route('report.edit.record.update', [$form->id, 0, 'new']), $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', [$form->id, 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = UploadedFile::fake()->create('test2.xls');
    }

    $this->put(route('report.edit.record.update', [$form->id, 1, 'new']), $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', [$form->id, 2]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $this->assertDatabaseHas('form_field_data', [
        'school_id' => $school->id,
        'form_field_id' => $fields[0]->id,
        'record' => 0,
        'data' => 'test.doc',
    ]);

    $this->assertDatabaseHas('form_field_data', [
        'school_id' => $school->id,
        'form_field_id' => $fields[0]->id,
        'record' => 1,
        'data' => 'test2.xls',
    ]);

    expect(Storage::exists("report/{$form->id}/school/{$school->id}/0/{$fields[0]->id}"))->toBeTrue();
    expect(Storage::exists("report/{$form->id}/school/{$school->id}/1/{$fields[0]->id}"))->toBeTrue();
});

it('can upload a file on a report as teacher (not in teachers table) (form accepts teachers and all teachers) (no multiple)', function () {

    test_cas_logged_in_as_teacher();

    Storage::fake('local');

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(1)
                ->state([
                    'sort_id' => 1,
                    'type' => 5,
                    'listvalues' => '',
                    'options' => '{"filetype":{"value":"-1","custom_value":".doc"}}',
                ]
                ),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    $post_data['f'.$fields[0]->id] = UploadedFile::fake()->create('test.doc');

    $this->put(route('report.update', $form->id), $post_data)
        ->assertRedirect(route('report.index'));

    $other_teacher = OtherTeacher::first();

    $this->assertDatabaseHas('form_field_data', [
        'form_field_id' => $fields[0]->id,
        'record' => 0,
        'data' => 'test.doc',
    ]);

    expect(Storage::exists("report/{$form->id}/other_teacher/{$other_teacher->id}/0/{$fields[0]->id}"))->toBeTrue();
});

it('can upload a file on a report as teacher (not in teachers table) (form accepts teachers and all teachers) (multiple)', function () {

    test_cas_logged_in_as_teacher();

    Storage::fake('local');

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(1)
                ->state([
                    'sort_id' => 1,
                    'type' => 5,
                    'listvalues' => '',
                    'options' => '{"filetype":{"value":"-1","custom_value":".doc,.xls"}}',
                ]
                ),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    $post_data['f'.$fields[0]->id] = UploadedFile::fake()->create('test.doc');

    $this->put(route('report.edit.record.update', [$form->id, 0, 'new']), $post_data)
        ->assertRedirect(route('report.edit.record', [$form->id, 1]));

    $post_data['f'.$fields[0]->id] = UploadedFile::fake()->create('test2.xls');

    $this->put(route('report.edit.record.update', [$form->id, 1, 'new']), $post_data)
        ->assertRedirect(route('report.edit.record', [$form->id, 2]));

    $other_teacher = OtherTeacher::first();
    $this->assertDatabaseHas('form_field_data', [
        'form_field_id' => $fields[0]->id,
        'record' => 0,
        'data' => 'test.doc',
    ]);

    $this->assertDatabaseHas('form_field_data', [
        'form_field_id' => $fields[0]->id,
        'record' => 1,
        'data' => 'test2.xls',
    ]);

    expect(Storage::exists("report/{$form->id}/other_teacher/{$other_teacher->id}/0/{$fields[0]->id}"))->toBeTrue();
    expect(Storage::exists("report/{$form->id}/other_teacher/{$other_teacher->id}/1/{$fields[0]->id}"))->toBeTrue();
});

it('can upload a file on a report as teacher (in teachers table) (form accepts teachers and all teachers) (no multiple)', function () {

    test_cas_logged_in_as_teacher();

    Storage::fake('local');

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(1)
                ->state([
                    'sort_id' => 1,
                    'type' => 5,
                    'listvalues' => '',
                    'options' => '{"filetype":{"value":"-1","custom_value":".doc"}}',
                ]
                ),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    $post_data['f'.$fields[0]->id] = UploadedFile::fake()->create('test.doc');

    $this->put(route('report.update', $form->id), $post_data)
        ->assertRedirect(route('report.index'));

    $other_teacher = OtherTeacher::first();

    $this->assertDatabaseHas('form_field_data', [
        'form_field_id' => $fields[0]->id,
        'record' => 0,
        'data' => 'test.doc',
    ]);

    expect(Storage::exists("report/{$form->id}/teacher/{$teacher->id}/0/{$fields[0]->id}"))->toBeTrue();
});

it('can upload a file on a report as teacher (in teachers table) (form accepts teachers and all teachers) (multiple)', function () {

    test_cas_logged_in_as_teacher();

    Storage::fake('local');

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(1)
                ->state([
                    'sort_id' => 1,
                    'type' => 5,
                    'listvalues' => '',
                    'options' => '{"filetype":{"value":"-1","custom_value":".doc,.xls"}}',
                ]
                ),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    $post_data['f'.$fields[0]->id] = UploadedFile::fake()->create('test.doc');

    $this->put(route('report.edit.record.update', [$form->id, 0, 'new']), $post_data)
        ->assertRedirect(route('report.edit.record', [$form->id, 1]));

    $post_data['f'.$fields[0]->id] = UploadedFile::fake()->create('test2.xls');

    $this->put(route('report.edit.record.update', [$form->id, 1, 'new']), $post_data)
        ->assertRedirect(route('report.edit.record', [$form->id, 2]));

    $other_teacher = OtherTeacher::first();
    $this->assertDatabaseHas('form_field_data', [
        'form_field_id' => $fields[0]->id,
        'record' => 0,
        'data' => 'test.doc',
    ]);

    $this->assertDatabaseHas('form_field_data', [
        'form_field_id' => $fields[0]->id,
        'record' => 1,
        'data' => 'test2.xls',
    ]);

    expect(Storage::exists("report/{$form->id}/teacher/{$teacher->id}/0/{$fields[0]->id}"))->toBeTrue();
    expect(Storage::exists("report/{$form->id}/teacher/{$teacher->id}/1/{$fields[0]->id}"))->toBeTrue();
});

it('can keep already saved file in a form (no multiple)', function () {

    test_cas_logged_in();

    Storage::fake('local');

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 5,
                        'listvalues' => '',
                        'options' => json_encode([
                            'filetype' => [
                                'value' => '-1',
                                'custom_value' => '*.jpg',
                            ],
                        ]),
                        'required' => true,
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $field = $form->form_fields()->first();
    $post_data = [];
    $post_data['f'.$field->id] = UploadedFile::fake()->create('test.jpg');

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $post_data = [];
    $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');
    $this->assertDatabaseHas('form_field_data', [
        'school_id' => $school->id,
        'form_field_id' => $field->id,
        'data' => 'test.jpg',
    ]);
});

it('can keep already saved file in a form (multiple)', function () {

    test_cas_logged_in();

    Storage::fake('local');

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 5,
                        'listvalues' => '',
                        'options' => json_encode([
                            'filetype' => [
                                'value' => '-1',
                                'custom_value' => '*.jpg',
                            ],

                        ]),
                        'required' => true,
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $field = $form->form_fields()->first();
    $post_data = [];
    $post_data['f'.$field->id] = UploadedFile::fake()->create('test.jpg');

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', [$form, 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    $this->put('/report/'.$form->id.'/edit/0/update/1', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', [$form, 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');
    $this->assertDatabaseHas('form_field_data', [
        'school_id' => $school->id,
        'form_field_id' => $field->id,
        'data' => 'test.jpg',
        'record' => 0,
    ]);
});

it('can download already saved file in a form', function () {

    test_cas_logged_in();

    Storage::fake('local');

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 5,
                        'listvalues' => '',
                        'options' => json_encode([
                            'filetype' => [
                                'value' => '-1',
                                'custom_value' => '*.jpg',
                            ],
                        ]),
                        'required' => true,
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $field = $form->form_fields()->first();
    $post_data = [];
    $post_data['f'.$field->id] = UploadedFile::fake()->create('test.jpg');

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $this->get("/download/{$form->id}/{$field->id}/0")
        ->assertDownload('test.jpg');
});

it('cannot download already saved file in a closed form', function () {

    test_cas_logged_in();

    Storage::fake('local');

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 5,
                        'listvalues' => '',
                        'options' => json_encode([
                            'filetype' => [
                                'value' => '-1',
                                'custom_value' => '*.jpg',
                            ],
                        ]),
                        'required' => true,
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $field = $form->form_fields()->first();
    $post_data = [];
    $post_data['f'.$field->id] = UploadedFile::fake()->create('test.jpg');

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $form->active = false;
    $form->save();

    $this->get("/download/{$form->id}/{$field->id}/0")
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('validates download links', function ($type) {
    Storage::fake('local');

    if ($type === 'school') {
        test_cas_logged_in();

        $school = School::factory()->for(User::factory())->create([
            'name' => 'Test School',
            'username' => '999',
        ]);

        $form = Form::factory()
            ->for(User::factory()->admin())
            ->has(
                FormField::factory()
                    ->state(new Sequence(function ($sequence) {
                        return [
                            'sort_id' => $sequence->index,
                            'type' => 5,
                            'listvalues' => '',
                            'options' => json_encode([
                                'filetype' => [
                                    'value' => '-1',
                                    'custom_value' => '*.jpg',
                                ],
                            ]),
                            'required' => true,
                        ];
                    })),
                'form_fields'
            )
            ->create([
                'multiple' => false,
                'active' => true,
            ]);

        $form->schools()->attach($school);
        $form->save();
    } elseif ($type === 'teacher' || $type === 'other_teacher') {
        test_cas_logged_in_as_teacher();

        if ($type === 'teacher') {
            Teacher::factory()->create(['am' => '123456']);
        }

        $form = Form::factory()
            ->for(User::factory()->admin())
            ->has(
                FormField::factory()
                    ->state(new Sequence(function ($sequence) {
                        return [
                            'sort_id' => $sequence->index,
                            'type' => 5,
                            'listvalues' => '',
                            'options' => json_encode([
                                'filetype' => [
                                    'value' => '-1',
                                    'custom_value' => '*.jpg',
                                ],
                            ]),
                            'required' => true,
                        ];
                    })),
                'form_fields'
            )
            ->create([
                'multiple' => false,
                'active' => true,
            ]);

        $form->for_teachers = true;
        if ($type === 'other_teacher') {
            $form->for_all_teachers = true;
        }
        $form->save();
    }

    $field = $form->form_fields()->first();
    $post_data = [];
    $post_data['f'.$field->id] = UploadedFile::fake()->create('test.jpg');

    $response = $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $this->get("/download/invalid/{$field->id}/0")
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->get("/download/{$form->id}/invalid/0")
        ->assertNotFound();

    $this->get("/download/{$form->id}/999/0")
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Το αρχείο δεν βρέθηκε');

    $this->get("/download/{$form->id}/{$field->id}/invalid")
        ->assertNotFound();

    $this->get("/download/{$form->id}/{$field->id}/999")
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Το αρχείο δεν βρέθηκε');

    $this->get("/download/{$form->id}/{$field->id}/0")
        ->assertDownload('test.jpg');
})->with(['school', 'teacher', 'other_teacher']);
