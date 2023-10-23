<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\Option;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCasManager;

beforeEach(function() {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();

    $this->app->singleton('cas', function () {
        return new TestCasManager();
    });

    test_cas_null();
});

it('can access reports as teacher of the directorate (in teachers table)', function() {

    test_cas_logged_in_as_teacher();

    // Πρόσθεσε τον καθηγητή στους εκπαιδευτικούς της Διεύθυνσης
    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true
    ]);

    $this->get('/report')
        ->assertOK()
        ->assertDontSee('Σφάλμα')
        ->assertSee('Δεν βρέθηκαν φόρμες');

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'title' => 'Direct',
            'active' => true,
            'for_teachers' => true,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertSee('Direct');

        $this->get('/report/'.$form->id)
            ->assertOK();
});

it('cannot access reports as user logged in through cas (not in teachers table)', function() {

    test_cas_logged_in_as_teacher();

    $this->get('/report')
        ->assertOK()
        ->assertDontSee('Σφάλμα')
        ->assertSee('Δεν βρέθηκαν φόρμες');

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'title' => 'Direct',
            'active' => true,
            'for_teachers' => true,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertSee('Δεν βρέθηκαν φόρμες');

        $this->get('/report/'.$form->id)
            ->assertRedirect(route('report.index'))
            ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός που δεν ανήκει στη Διεύθυνση.');
});


it('cannot access inactive reports as user logged in through cas', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $this->get('/report')
        ->assertOK()
        ->assertDontSee('Σφάλμα')
        ->assertSee('Δεν βρέθηκαν φόρμες');

        $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'title' => 'Direct',
            'active' => false,
        ]);

        $form2 = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'title' => 'Indirect',
            'active' => false,
        ]);

        $school_category = SchoolCategory::factory()->create([
            'name' => 'Test Category'
        ]);

        $school->categories()->attach($school_category);
        $school->save();

        $form->schools()->attach($school);
        $form->save();

        $form2->school_categories()->attach($school_category);
        $form2->save();

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertDontSee('Direct')
            ->assertDontSee('Indirect');
});

it('cannot show a report that doesn\'t exist as user logged in through cas', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot show a report as user logged in through cas (no permission - user not in schools)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    $this->get('/report/'.$form->id)
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot show a report as user logged in through cas (no permission - user has no rights)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $this->get('/report/'.$form->id)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can show a report as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => false,
            'active' => true,
         ])
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => true,
            'active' => true,
        ])
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot show an inactive report as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => false,
            'active' => false,
         ])
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => true,
            'active' => false,
        ])
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('can show a report as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => false,
            'active' => true,
        ])
        ->create();

    $school->categories()->attach($school_category);
    $school->save();

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => true,
            'active' => true,
        ])
        ->create();

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);

});

it('cannot show an inactive report as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => false,
            'active' => false,
        ])
        ->create();

    $school->categories()->attach($school_category);
    $school->save();

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $form = Form::factory()
        ->for(User::factory()->admin())
        ->has(
            FormField::factory()
                ->count(5)
                ->state(new Sequence(function ($sequence) {
                    return [
                        'sort_id' => $sequence->index,
                        'type' => 0,
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->state([
            'multiple' => true,
            'active' => false,
        ])
        ->create();

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report that doesn\'t exist as user logged in through cas', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/0/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report as user logged in through cas (no permission - user not in schools)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot edit a report as user logged in through cas (no permission - user has no rights)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can edit a report as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id.'/edit')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot edit an inactive report as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => false,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('can edit a report as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => true,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save([
        'active' => true,
    ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot edit an inactive report as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'active' => false,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a record of a report that doesn\'t exist as user logged in through cas', function() {

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
                        'listvalues' => ''
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

    $this->get('/report/0/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report record as user logged in through cas (no permission - user not in schools)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot edit a report record as user logged in through cas (no permission - user has no rights)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can edit a report record as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot edit a record of an inactive report as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('can edit a report record as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot edit a record of an inactive report as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => false,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report record as user logged in through cas (no permission - user not in schools) (no multiple)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot edit a report record as user logged in through cas (no permission - user has no rights) (no multiple)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('cannot edit a report record as user logged in through cas (direct relation) (no multiple)', function() {

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
                        'listvalues' => ''
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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');
});

it('cannot edit a report record as user logged in through cas (indirect relation) (no multiple)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');
});

it('cannot update a report as user logged in through cas (no permission - user not in schools) (no multiple)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot update a report as user logged in through cas (no permission - user has no rights) (no multiple)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can update a report as user logged in through cas (direct relation) (no multiple)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data2',
        ]);
});

it('cannot update an inactive report as user logged in through cas (direct relation) (no multiple)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('can update a report as user logged in through cas (indirect relation) (no multiple)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');
});

it('cannot update an inactive report as user logged in through cas (indirect relation) (no multiple)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot update a report that doesn\'t exist as user logged in through cas (no multiple)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/0', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot update a report as user logged in through cas (no permission - user not in schools)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot update a report as user logged in through cas (no permission - user has no rights)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can update a report as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 2]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data3';
    }

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data4';
    }

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 4]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data5';
    }

    $this->put('/report/'.$form->id.'/edit/4/update/whatever', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data',
            'record' => 0,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data2',
            'record' => 1,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data3',
            'record' => 2,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data4',
            'record' => 3,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data5',
            'record' => 4,
        ]);
});

it('cannot update an inactive report as user logged in through cas (direct relation)', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/4/update/whatever', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

});

it('can update a report as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 2]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data3';
    }

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data4';
    }

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 4]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data5';
    }

    $this->put('/report/'.$form->id.'/edit/4/update/whatever', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data',
            'record' => 0,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data2',
            'record' => 1,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data3',
            'record' => 2,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data4',
            'record' => 3,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'school_id' => $school->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data5',
            'record' => 4,
        ]);
});

it('cannot update an inactive report as user logged in through cas (indirect relation)', function() {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category'
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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');

    $this->put('/report/'.$form->id.'/edit/4/update/whatever', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot update a report that doesn\'t exist as user logged in through cas', function() {

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
                        'listvalues' => ''
                    ];
                })),
            'form_fields'
        )
        ->create([
            'multiple' => true,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/0/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});
