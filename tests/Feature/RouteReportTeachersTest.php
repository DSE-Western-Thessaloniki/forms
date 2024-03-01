<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldData;
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

it('cannot access reports as teacher (in teachers table) (form doesn\'t accept teachers)', function() {

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
            'for_teachers' => false,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertDontSee('Direct');

        $this->get('/report/'.$form->id)
            ->assertRedirect(route('report.index'))
            ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});
it('cannot access reports as teacher (not in teachers table) (form doesn\'t accept teachers)', function() {

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
            'for_teachers' => false,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertDontSee('Direct');

        $this->get('/report/'.$form->id)
            ->assertRedirect(route('report.index'))
            ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});

it('can access reports as teacher (in teachers table) (form accepts teachers, not all teachers)', function() {

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
            'for_all_teachers' => false,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertSee('Direct');

        $this->get('/report/'.$form->id)
            ->assertOK();
});

it('can access reports as teacher (in teachers table) (form accepts teachers and all teachers)', function() {

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
            'for_all_teachers' => true,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertSee('Direct');

        $this->get('/report/'.$form->id)
            ->assertOK();
});

it('cannot access reports as teacher (not in teachers table) (form accepts teachers, not all teachers)', function() {

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
            'for_all_teachers' => false,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertSee('Δεν βρέθηκαν φόρμες');

        $this->get('/report/'.$form->id)
            ->assertRedirect(route('report.index'))
            ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός που δεν ανήκει στη Διεύθυνση.');
});

it('can access reports as teacher (not in teachers table) (form accepts teachers and all teachers)', function() {

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
            'for_all_teachers' => true,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertSee('Direct');

        $this->get('/report/'.$form->id)
            ->assertOK();
});

it('cannot see inactive reports as teacher', function() {

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
            'active' => false,
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

        $this->get('/report')
            ->assertOK()
            ->assertDontSee('Σφάλμα')
            ->assertDontSee('Direct');
});

it('cannot show a report that doesn\'t exist as teacher', function() {

    test_cas_logged_in_as_teacher();

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

    $this->get('/report/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report that doesn\'t exist as teacher', function() {

    test_cas_logged_in_as_teacher();

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

    $this->get('/report/0/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report as teacher (in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true
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
            'for_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});

it('cannot edit a report as teacher (not in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});

it('cannot edit an inactive report as teacher (not in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit an inactive report as teacher (not in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit an inactive report as teacher (not in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit an inactive report as teacher (in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit an inactive report as teacher (in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit an inactive report as teacher (in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a record of a report that doesn\'t exist as teacher', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $this->get('/report/0/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot edit a report record as teacher (not in teachers) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός που δεν ανήκει στη Διεύθυνση.');
});

it('can edit a report record as teacher (not in teachers) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertSee($form->title);
});

it('can edit a report record as teacher (in teachers) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertSee($form->title);
});

it('can edit a report record as teacher (in teachers) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertOk()
        ->assertSee($form->title);
});

it('cannot edit a record of an inactive report as teacher (not in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a record of an inactive report as teacher (not in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a record of an inactive report as teacher (not in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a record of an inactive report as teacher (in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a record of an inactive report as teacher (in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a record of an inactive report as teacher (in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot edit a report record as teacher (not in teachers) (form doesn\'t accept teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $response = $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');

});

it('cannot edit a report record as teacher (not in teachers) (form accepts teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $response = $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός που δεν ανήκει στη Διεύθυνση.');

});

it('cannot edit a report record as teacher (not in teachers) (form accepts teachers and all teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $response = $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');

});

it('cannot edit a report record as teacher (in teachers) (form doesn\'t accept teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $response = $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');

});

it('cannot edit a report record as teacher (in teachers) (form accepts teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $response = $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');

});

it('cannot edit a report record as teacher (in teachers) (form accepts teachers and all teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $response = $this->get('/report/'.$form->id.'/edit/0')
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα δεν δέχεται πολλαπλές απαντήσεις');

});

it('cannot update a report as teacher (not in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});

it('cannot update a report as teacher (not in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός που δεν ανήκει στη Διεύθυνση.');
});

it('can update a report as teacher (not in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data ' . $field->id;
    }

    $response = $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'));

    $this->assertDatabaseCount('form_field_data', 5);
    foreach ($fields as $field) {
        $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $field->id,
            'data' => 'Test data ' . $field->id,
        ]);
    }

});

it('cannot update a report as teacher (in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});

it('can update a report as teacher (in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data ' . $field->id;
    }

    $response = $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'));

    $this->assertDatabaseCount('form_field_data', 5);
    foreach ($fields as $field) {
        $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $field->id,
            'data' => 'Test data ' . $field->id,
        ]);
    }
});

it('can update a report as teacher (in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data ' . $field->id;
    }

    $response = $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'));

    $this->assertDatabaseCount('form_field_data', 5);
    foreach ($fields as $field) {
        $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $field->id,
            'data' => 'Test data ' . $field->id,
        ]);
    }

});

it('cannot update an inactive report as teacher (not in teachers table) (form doesn\'t accept teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update an inactive report as teacher (not in teachers table) (form accepts teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update an inactive report as teacher (not in teachers table) (form accepts teachers and all teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update an inactive report as teacher (in teachers table) (form doesn\'t accept teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update an inactive report as teacher (in teachers table) (form accepts teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update an inactive report as teacher (in teachers table) (form accepts teachers and all teachers) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id, $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
});

it('cannot update a report that doesn\'t exist as teacher (not in teachers table) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

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

    $this->put('/report/0', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot update a report that doesn\'t exist as teacher (in teachers table) (no multiple)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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

    $this->put('/report/0', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Λάθος αναγνωριστικό φόρμας');
});

it('cannot update a report record as teacher (not in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});

it('cannot update a report record as teacher (not in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός που δεν ανήκει στη Διεύθυνση.');
});

it('can update a report record as teacher (not in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data ' . $field->id;
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect('/report/'.$form->id.'/edit/1')
        ->assertDontSee('Σφάλμα');

    // Θα έχουμε 10 εγγραφές γιατί κατά την ενημέρωση εγγραφών με /new
    // προετοιμάζουμε και την επόμενη καταχώρηση με null στα data
    $this->assertDatabaseCount('form_field_data', 10);
    foreach ($fields as $field) {
        $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $field->id,
            'data' => 'Test data ' . $field->id,
        ]);
    }

});

it('cannot update a report record as teacher (in teachers table) (form doesn\'t accept teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => false,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect(route('report.index'))
        ->assertSessionHas('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
});

it('can update a report record as teacher (in teachers table) (form accepts teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => false,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data ' . $field->id;
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect('/report/'.$form->id.'/edit/1')
        ->assertDontSee('Σφάλμα');

    // Θα έχουμε 10 εγγραφές γιατί κατά την ενημέρωση εγγραφών με /new
    // προετοιμάζουμε και την επόμενη καταχώρηση με null στα data
    $this->assertDatabaseCount('form_field_data', 10);
    foreach ($fields as $field) {
        $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $field->id,
            'data' => 'Test data ' . $field->id,
        ]);
    }
});

it('can update a report record as teacher (in teachers table) (form accepts teachers and all teachers)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data ' . $field->id;
    }

    $this->put('/report/'.$form->id.'/edit/0/update/new', $post_data)
        ->assertRedirect('/report/'.$form->id.'/edit/1')
        ->assertDontSee('Σφάλμα');

    // Θα έχουμε 10 εγγραφές γιατί κατά την ενημέρωση εγγραφών με /new
    // προετοιμάζουμε και την επόμενη καταχώρηση με null στα data
    $this->assertDatabaseCount('form_field_data', 10);
    foreach ($fields as $field) {
        $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $field->id,
            'data' => 'Test data ' . $field->id,
        ]);
    }

});

it('can traverse a report (with multiple) as teacher (not in teachers table)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

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
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data',
            'record' => 0,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data2',
            'record' => 1,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data3',
            'record' => 2,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data4',
            'record' => 3,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data5',
            'record' => 4,
        ]);
});

it('can traverse a report (with multiple) as teacher (in teachers table)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true,
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

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
            'teacher_id' => $teacher->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data',
            'record' => 0,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'teacher_id' => $teacher->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data2',
            'record' => 1,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'teacher_id' => $teacher->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data3',
            'record' => 2,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'teacher_id' => $teacher->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data4',
            'record' => 3,
        ]);
    $this->assertDatabaseHas('form_field_data', [
            'teacher_id' => $teacher->id,
            'form_field_id' => $fields[0]->id,
            'data' => 'Test data5',
            'record' => 4,
        ]);
});

it('cannot traverse an inactive report (with multiple) as teacher (not in teachers table)', function() {

    test_cas_logged_in_as_teacher();

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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

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

it('cannot traverse an inactive report (with multiple) as teacher (in teachers table)', function() {

    test_cas_logged_in_as_teacher();

    $teacher = Teacher::factory()->create([
        'am' => '123456',
        'active' => true
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
            'for_teachers' => true,
            'for_all_teachers' => true,
        ]);

    $fields = $form->form_fields()->get();
    $post_data = array();
    foreach ($fields as $field) {
        $post_data['f'.$field->id] = 'Test data';
    }

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
