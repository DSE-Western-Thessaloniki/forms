<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldData;
use App\Models\Option;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;

it('can create a new user', function () {
    $user = User::factory()->create();

    $this->assertInstanceOf('App\Models\User', $user);
    $this->assertDatabaseHas('users', ['updated_by' => 0]);
});

it('can check for admin', function () {
    $user1 = User::factory()
        ->admin()
        ->create();
    $user2 = User::factory()
        ->has(
            Role::factory()
                ->state(['name' => 'Author'])
        )
        ->create();

    $this->assertTrue($user1->isAdministrator());
    $this->assertFalse($user2->isAdministrator());
});

it('can create a new role', function () {
    $role = Role::factory()
        ->state([ 'name' => 'Test Role' ])
        ->create();
    expect($role)->toMatchArray([ 'name' => 'Test Role' ]);
});

it('can create a new form', function () {
    $form = Form::factory()
        ->for(User::factory())
        ->has(
            School::factory()
                ->state(
                    [
                        'name' => 'School'
                    ]
                )
                ->for(User::factory())
        )
        ->has(
            SchoolCategory::factory()
                ->state(['name' => 'ΓΕΛ']),
            'school_categories'
        )
        ->create();
    $this->assertInstanceOf('App\Models\Form', $form);
    $this->assertInstanceOf('App\Models\School', $form->schools()->first());
    $this->assertInstanceOf('App\Models\SchoolCategory', $form->school_categories()->first());
});

it('can create form fields for a form', function () {
    $form = Form::factory()
        ->for(User::factory())
        ->has(
            FormField::factory(3)
                ->state(
                    new Sequence(
                        fn ($sequence) => [
                            'sort_id' => $sequence->index,
                            ]
                    )
                ),
            'form_fields'
        )
        ->create();
    $this->assertInstanceOf('App\Models\FormField', $form->form_fields()->first());
    $this->assertEquals($form->getAttributes(), $form->form_fields()->first()->form->getAttributes());
    $this->assertCount(3, $form->form_fields()->get());
});

it('can add data to form fields', function () {
    $user = User::factory()->create();
    $form = Form::factory()
        ->for($user)
        ->has(
            FormField::factory(3)
                ->state(
                    new Sequence(
                        fn ($sequence) => [
                            'sort_id' => $sequence->index,
                            ]
                    )
                )
                ->has(
                    FormFieldData::factory()
                        ->state(
                            [
                                'data' => 'test'
                            ]
                        )
                        ->for(
                            School::factory()
                                ->state(
                                    [
                                        'name' => 'test school'
                                    ]
                                )
                                ->for($user)
                        ),
                    'field_data'
                ),
            'form_fields'
        )
        ->create();
    foreach ($form->form_fields()->get() as $form_field) {
        expect($form_field->field_data()->first()->data)->toBe('test');
        $this->assertEquals(
            $form_field->getAttributes(),
            $form_field->field_data()->first()->form_field->getAttributes()
        );
    }
});
