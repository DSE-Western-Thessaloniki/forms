<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\Option;
use App\Models\School;
use App\Models\SchoolCategory;
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

it('gets cas/login without logging in', function ($url) {

    test_cas_not_logged_in();

    $response = $this->get('/report');
    $response->assertStatus(500);
    expect($response->baseResponse->exception->getMessage())->toBe('Must authenticate with CAS');
})->with('sch_routes');

it('gets cas/login logged in as user', function ($url) {

    test_cas_not_logged_in();

    $response = $this->actingAs(User::factory()->admin()->create())->get('/report');
    $response->assertStatus(500);
    expect($response->baseResponse->exception->getMessage())->toBe('Must authenticate with CAS');
    $response = $this->actingAs(User::factory()->author()->create())->get('/report');
    $response->assertStatus(500);
    expect($response->baseResponse->exception->getMessage())->toBe('Must authenticate with CAS');
    $response = $this->actingAs(User::factory()->user()->create())->get('/report');
    $response->assertStatus(500);
    expect($response->baseResponse->exception->getMessage())->toBe('Must authenticate with CAS');
})->with('sch_routes');

it('denies access to users not in the schools table', function () {

    test_cas_logged_in();

    $this->get('/report')->assertOk()->assertSee('Σφάλμα');
});

it('can access reports as user logged in through cas', function () {

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
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'title' => 'Direct',
            'active' => true,
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
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'title' => 'Indirect',
            'active' => true,
        ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category',
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
        ->assertSee('Direct')
        ->assertSee('Indirect');

    $this->get('/report/'.$form->id)
        ->assertOK();

    $this->get('/report/'.$form2->id)
        ->assertOK();
});

it('cannot access inactive reports as user logged in through cas', function () {

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
                        'listvalues' => '',
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
                        'listvalues' => '',
                    ];
                })),
            'form_fields'
        )
        ->create([
            'title' => 'Indirect',
            'active' => false,
        ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category',
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

it('cannot show a report that doesn\'t exist as user logged in through cas', function () {

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
            'active' => true,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot show a report as user logged in through cas (no permission - user not in schools)', function () {

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
            'active' => true,
        ]);

    $this->get('/report/'.$form->id)
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot show a report as user logged in through cas (no permission - user has no rights)', function () {

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

it('can show a report as user logged in through cas (direct relation)', function () {

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
                        'listvalues' => '',
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

it('cannot show an inactive report as user logged in through cas (direct relation)', function () {

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
                        'listvalues' => '',
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

it('can show a report as user logged in through cas (indirect relation)', function () {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category',
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
                        'listvalues' => '',
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

it('cannot show an inactive report as user logged in through cas (indirect relation)', function () {

    test_cas_logged_in();

    $school = School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $school_category = SchoolCategory::factory()->create([
        'name' => 'Test Category',
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
                        'listvalues' => '',
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

it('cannot edit a report that doesn\'t exist as user logged in through cas', function () {

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
            'active' => true,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/0/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report as user logged in through cas (no permission - user not in schools)', function () {

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
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot edit a report as user logged in through cas (no permission - user has no rights)', function () {

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
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can edit a report as user logged in through cas (direct relation)', function () {

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

it('cannot edit an inactive report as user logged in through cas (direct relation)', function () {

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
            'active' => false,
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('can edit a report as user logged in through cas (indirect relation)', function () {

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

it('cannot edit an inactive report as user logged in through cas (indirect relation)', function () {

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
            'active' => false,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a record of a report that doesn\'t exist as user logged in through cas', function () {

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

    $this->get('/report/0/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report record as user logged in through cas (no permission - user not in schools)', function () {

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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot edit a report record as user logged in through cas (no permission - user has no rights)', function () {

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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can edit a report record as user logged in through cas (direct relation)', function () {

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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot edit a record of an inactive report as user logged in through cas (direct relation)', function () {

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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('can edit a report record as user logged in through cas (indirect relation)', function () {

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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot edit a record of an inactive report as user logged in through cas (indirect relation)', function () {

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
            'active' => false,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a report record as user logged in through cas (no permission - user not in schools) (no multiple)', function () {

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
            'multiple' => false,
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot edit a report record as user logged in through cas (no permission - user has no rights) (no multiple)', function () {

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
            'multiple' => false,
            'active' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('cannot edit a report record as user logged in through cas (direct relation) (no multiple)', function () {

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

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');
});

it('cannot edit a report record as user logged in through cas (indirect relation) (no multiple)', function () {

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
            'multiple' => false,
            'active' => true,
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');
});

it('cannot update a report as user logged in through cas (no permission - user not in schools) (no multiple)', function () {

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
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot update a report as user logged in through cas (no permission - user has no rights) (no multiple)', function () {

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
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can update a report as user logged in through cas (direct relation) (no multiple)', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $post_data = [];
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

it('cannot update an inactive report as user logged in through cas (direct relation) (no multiple)', function () {

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
            'active' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('can update a report as user logged in through cas (indirect relation) (no multiple)', function () {

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
            'multiple' => false,
            'active' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');
});

it('cannot update an inactive report as user logged in through cas (indirect relation) (no multiple)', function () {

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
            'multiple' => false,
            'active' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update a report that doesn\'t exist as user logged in through cas (no multiple)', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/0', $post_data)
        ->assertForbidden();
});

it('cannot update a report as user logged in through cas (no permission - user not in schools)', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertOk()
        ->assertSee('Σφάλμα');
});

it('cannot update a report as user logged in through cas (no permission - user has no rights)', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
});

it('can update a report as user logged in through cas (direct relation)', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 2]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data3';
    }

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data4';
    }

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 4]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
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

it('cannot update an inactive report as user logged in through cas (direct relation)', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/4/update/whatever', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

});

it('can update a report as user logged in through cas (indirect relation)', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 2]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data3';
    }

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 1]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data2';
    }

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('success', 'Τα στοιχεία αποθηκεύτηκαν στη φόρμα επιτυχώς');

    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data4';
    }

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertDontSee('Σφάλμα')
        ->assertRedirect(route('report.edit.record', ['report' => $form->id, 'record' => 4]))
        ->assertSessionHas('success', 'Η αναφορά ενημερώθηκε');

    $post_data = [];
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

it('cannot update an inactive report as user logged in through cas (indirect relation)', function () {

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
            'active' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/1/update/next', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/2/update/prev', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/1/update/exit', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/3/update/4', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');

    $this->put('/report/'.$form->id.'/edit/4/update/whatever', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update a report that doesn\'t exist as user logged in through cas', function () {

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

    $fields = $form->form_fields()->get();
    $post_data = [];
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $form->schools()->attach($school);
    $form->save();

    $this->put('/report/0/edit/0/update/new', $post_data)
        ->assertForbidden();
});

it('can keep already saved file in a form (no multiple)', function () {

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
