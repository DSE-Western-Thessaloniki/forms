<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\Option;
use App\Models\OtherTeacher;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\OptionSeeder;
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
