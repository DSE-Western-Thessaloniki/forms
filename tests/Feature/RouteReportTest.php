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

it('gets cas/login without logging in', function($url) {

    test_cas_not_logged_in();

    $response = $this->get('/report');
    $response->assertStatus(500);
    expect($response->baseResponse->exception->getMessage())->toBe('Must authenticate with CAS');
})->with('sch_routes');

it('gets cas/login logged in as user', function($url) {

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

it('denies access to users not in the schools table', function() {

    test_cas_logged_in();

    $this->get('/report')->assertSee('Σφάλμα');
});

it('can access reports as user logged in through cas', function() {

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
            'title' => 'Direct'
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
            'title' => 'Indirect'
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
        ->assertSee('Direct')
        ->assertSee('Indirect');
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
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot show a report as user logged in through cas (no permission)', function() {

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
        ->create();

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
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id)
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
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
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/0/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report as user logged in through cas (no permission)', function() {

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
        ->create();

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
        ->create();

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id.'/edit')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
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
        ->create();

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
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
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/0/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report record as user logged in through cas (no permission)', function() {

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
        ]);

    $form->schools()->attach($school);
    $form->save();

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
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
        ]);

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertDontSee('Σφάλμα')
        ->assertSee($form->title)
        ->assertSee($form->notes);
});

it('cannot edit a report record as user logged in through cas (no permission) (no multiple)', function() {

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
        ->create();

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
        ->create();

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
        ->create();

    $form->school_categories()->attach($school_category);
    $form->save();

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');
});
