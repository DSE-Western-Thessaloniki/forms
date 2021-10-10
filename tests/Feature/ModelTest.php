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

it('can find users with a specific role', function () {
    $role = Role::factory()->state(['name' => 'Test'])->create();
    User::factory()
        ->count(2)
        ->create()
        ->each(function ($user) use ($role) {
            $user->roles()->attach($role);
        });
    expect(Role::where('name', 'Test')->first()->users->count())->toBe(2);
});

it('can find forms available for a school', function () {
    $user = User::factory()
        ->admin()
        ->create();
    $school = School::factory()
        ->state(['name' => 'test school'])
        ->for($user)
        ->create();
    Form::factory()
        ->for($user)
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
        ->create()
        ->each(function ($form) use ($school) {
            $form->schools()->attach($school);
        });
    expect($school->forms->count())->toBe(1);
});

it('can find forms available for a school category', function () {
    $category = SchoolCategory::factory()
        ->state(['name' => 'Test Category'])
        ->create();
    Form::factory()
        ->count(2)
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
        ->create()
        ->each(function ($form) use ($category) {
            $form->school_categories()->attach($category);
        });
    expect($category->forms->count())->toBe(2);
});


it('can find form fields filled by a school', function () {
    $user = User::factory()
        ->admin()
        ->create();
    $school = School::factory()
        ->state(['name' => 'test school'])
        ->for($user)
        ->create();
    Form::factory()
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
                        ->for($school),
                    'field_data'
                ),
            'form_fields'
        )
        ->create()
        ->each(function ($form) use ($school) {
            $form->schools()->attach($school);
        });
    expect($school->field_data->count())->toBe(3);
});

it('can find the categories a school belongs to', function () {
    $categories = SchoolCategory::factory()
        ->count(3)
        ->state(new Sequence(fn ($sequence) => [
            'name' => $sequence->index,
        ]))
        ->create();
    $school = School::factory()
        ->state(['name' => 'test school'])
        ->for(User::factory())
        ->create();
    foreach ($categories as $category) {
        $school->categories()->attach($category);
    }
    expect($school->categories->count())->toBe(3);
});

it('can find schools of a specific category', function () {
    $categories = SchoolCategory::factory()
        ->count(3)
        ->state(new Sequence(fn ($sequence) => [
            'name' => $sequence->index,
        ]))
        ->create();
    $schools = School::factory()
        ->count(4)
        ->state(new Sequence(fn ($sequence) => [
            'name' => 'test school '.$sequence->index
        ]))
        ->for(User::factory())
        ->create();
    foreach ($schools as $index => $school) {
        if ($index === 0) {
            $school->categories()->attach($categories[$index]);
        }
        if (in_array($index, [1, 2])) {
            $school->categories()->attach($categories[1]);
        }
    }
    expect($categories[0]->schools()->count())->toBe(1);
    expect($categories[1]->schools()->count())->toBe(2);
    expect($categories[2]->schools()->count())->toBe(0);
});

