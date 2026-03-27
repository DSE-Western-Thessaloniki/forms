<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\Option;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCasManager;

beforeEach(function () {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();

    $this->app->singleton('cas', function () {
        return new TestCasManager;
    });

    test_cas_null();
});

it('cannot delete a record without being logged in through cas', function () {
    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $fields = $form->form_fields()->get();
    foreach ($fields as $field) {
        $field->field_data()->create([
            'school_id' => $school->id,
            'record' => 0,
            'data' => 'Test data',
        ]);
    }

    $this->delete('/report/'.$form->id.'/record/0')
        ->assertStatus(500);
});

it('cannot delete a record from a non-existent form', function () {
    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $this->delete('/report/0/record/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot delete a record from an inactive form', function () {
    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => false,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->delete('/report/'.$form->id.'/record/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot delete a record without permission (user not in schools)', function () {
    test_cas_logged_in();

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $this->delete('/report/'.$form->id.'/record/0')
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot delete a record without permission (user has no rights)', function () {
    test_cas_logged_in();

    School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $this->delete('/report/'.$form->id.'/record/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can delete a record from a form with multiple records (direct relation)', function () {
    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $fields = $form->form_fields()->get();
    foreach ($fields as $field) {
        $field->field_data()->create([
            'school_id' => $school->id,
            'record' => 0,
            'data' => 'Record 0',
        ]);
        $field->field_data()->create([
            'school_id' => $school->id,
            'record' => 1,
            'data' => 'Record 1',
        ]);
        $field->field_data()->create([
            'school_id' => $school->id,
            'record' => 2,
            'data' => 'Record 2',
        ]);
    }

    $this->delete('/report/'.$form->id.'/record/1')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 0]))
        ->assertSessionHas('success', 'Η εγγραφή διαγράφηκε');

    foreach ($fields as $field) {
        $this->assertDatabaseMissing('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $field->id,
            'data' => 'Record 1',
        ]);
        $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $field->id,
            'record' => 0,
            'data' => 'Record 0',
        ]);
        $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $field->id,
            'record' => 1,
            'data' => 'Record 2',
        ]);
    }
});

it('can delete a record from a form with multiple records (indirect relation)', function () {
    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category',
    ]);

    $school->categories()->attach($school_category);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $fields = $form->form_fields()->get();
    foreach ($fields as $field) {
        $field->field_data()->create([
            'school_id' => $school->id,
            'record' => 0,
            'data' => 'Record 0',
        ]);
        $field->field_data()->create([
            'school_id' => $school->id,
            'record' => 1,
            'data' => 'Record 1',
        ]);
    }

    $this->delete('/report/'.$form->id.'/record/0')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 0]))
        ->assertSessionHas('success', 'Η εγγραφή διαγράφηκε');

    foreach ($fields as $field) {
        $this->assertDatabaseMissing('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $field->id,
            'data' => 'Record 0',
        ]);
        $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $field->id,
            'record' => 0,
            'data' => 'Record 1',
        ]);
    }
});

it('can delete a record from a form without multiple records', function () {
    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => '',
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

    $fields = $form->form_fields()->get();
    foreach ($fields as $field) {
        $field->field_data()->create([
            'school_id' => $school->id,
            'record' => 0,
            'data' => 'Test data',
        ]);
    }

    $this->delete('/report/'.$form->id.'/record/0')
        ->assertRedirect(route('report.edit', ['report' => $form->id]))
        ->assertSessionHas('success', 'Η εγγραφή διαγράφηκε');

    foreach ($fields as $field) {
        $this->assertDatabaseMissing('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $field->id,
            'record' => 0,
        ]);
    }
});
