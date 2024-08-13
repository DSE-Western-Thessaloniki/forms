<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldData;
use App\Models\Option;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Database\Seeders\FormFieldDataSeeder;
use Database\Seeders\OptionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SchoolCategorySeeder;
use Database\Seeders\SchoolSeeder;
use Database\Seeders\UserSeeder;
use Subfission\Cas\Facades\Cas;
use Tests\TestCasManager;

beforeEach(function () {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();

    $this->app->singleton('cas', function () {
        return new TestCasManager();
    });

    Cas::shouldReceive('isAuthenticated')
        ->andReturn(false);
    Cas::shouldReceive('user')
        ->andReturn(null);
    Cas::shouldReceive('logout')
        ->andReturn(null);
    Cas::shouldReceive('client')
        ->andReturn(null);
});

it('can access the forms panel as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/form')->assertOk();
});

it('can access the forms panel as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/form')->assertOk();
});

it('can access the forms panel as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/form')->assertOk();
});

it('can access the forms panel as admin and filter list', function () {
    $admin = User::factory()->admin()->create();
    $testForms = Form::factory(2)->for($admin)->create(['active' => true]);

    $result = $this->actingAs($admin)->call('GET', '/admin/form', [
        'filter' => $testForms[0]->title,
    ])->assertOk();

    $result->assertSee($testForms[0]->title);
    $result->assertDontSee($testForms[1]->title);
});

it('can access a form\'s preview as user', function () {
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create(['active' => true]);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id)->assertOk();
});

it('can access a form\'s preview as author', function () {
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create(['active' => true]);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id)->assertOk();
});

it('can access a form\'s preview as admin', function () {
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create(['active' => true]);

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id)->assertOk();
});

it('cannot access form creation as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/form/create')->assertForbidden();
});

it('can access form creation as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/form/create')->assertOk();
});

it('can access form creation as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/form/create')->assertOk();
});

it('cannot create a form as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '']],
    ])->assertForbidden();
});

it('can create a form as author', function () {
    $author = User::factory()->author()->create();

    $response = $this->actingAs($author)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);
});

it('can create a form as admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);
});

it('can create a form with school categories as author', function () {
    $author = User::factory()->author()->create();

    $category1 = SchoolCategory::factory()->create(['name' => 'Test1']);
    $category2 = SchoolCategory::factory()->create(['name' => 'Test2']);

    $response = $this->actingAs($author)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'categories' => strval($category1->id).','.strval($category2->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category1->id,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category2->id,
    ]);
});

it('can create a form with school categories as admin', function () {
    $admin = User::factory()->admin()->create();

    $category1 = SchoolCategory::factory()->create(['name' => 'Test1']);
    $category2 = SchoolCategory::factory()->create(['name' => 'Test2']);

    $response = $this->actingAs($admin)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'categories' => strval($category1->id).','.strval($category2->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category1->id,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category2->id,
    ]);
});

it('can create a form with fake school categories as author', function () {
    $author = User::factory()->author()->create();

    $response = $this->actingAs($author)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'categories' => '0,1',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseMissing('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => 0,
    ]);

    $this->assertDatabaseMissing('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => 1,
    ]);
});

it('can create a form with fake school categories as admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'categories' => '0,1',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseMissing('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => 0,
    ]);

    $this->assertDatabaseMissing('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => 1,
    ]);
});

it('can create a form with schools as author', function () {
    $author = User::factory()->author()->create();

    $school1 = School::factory()->for($author)->create(['name' => 'Test School1']);
    $school2 = School::factory()->for($author)->create(['name' => 'Test School2']);

    $response = $this->actingAs($author)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'schools' => strval($school1->id).','.strval($school2->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school1->id,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school2->id,
    ]);
});

it('can create a form with schools as admin', function () {
    $admin = User::factory()->admin()->create();

    $school1 = School::factory()->for($admin)->create(['name' => 'Test School1']);
    $school2 = School::factory()->for($admin)->create(['name' => 'Test School2']);

    $response = $this->actingAs($admin)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'schools' => strval($school1->id).','.strval($school2->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school1->id,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school2->id,
    ]);
});

it('can create a form with fake schools as author', function () {
    $author = User::factory()->author()->create();

    $response = $this->actingAs($author)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'schools' => '0,1',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseMissing('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => 0,
    ]);

    $this->assertDatabaseMissing('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => 1,
    ]);
});

it('can create a form with fake schools as admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'schools' => '0,1',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseMissing('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => 0,
    ]);

    $this->assertDatabaseMissing('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => 0,
    ]);
});

it('can create a form with schools and school categories as author', function () {
    $author = User::factory()->author()->create();

    $category1 = SchoolCategory::factory()->create(['name' => 'Test1']);
    $category2 = SchoolCategory::factory()->create(['name' => 'Test2']);

    $school1 = School::factory()->for($author)->create(['name' => 'Test School1']);
    $school2 = School::factory()->for($author)->create(['name' => 'Test School2']);

    $response = $this->actingAs($author)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'categories' => strval($category1->id).','.strval($category2->id),
        'schools' => strval($school1->id).','.strval($school2->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category1->id,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category2->id,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school1->id,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school2->id,
    ]);
});

it('can create a form with schools and school categories as admin', function () {
    $admin = User::factory()->admin()->create();

    $category1 = SchoolCategory::factory()->create(['name' => 'Test1']);
    $category2 = SchoolCategory::factory()->create(['name' => 'Test2']);

    $school1 = School::factory()->for($admin)->create(['name' => 'Test School1']);
    $school2 = School::factory()->for($admin)->create(['name' => 'Test School2']);

    $response = $this->actingAs($admin)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '', 'sort_id' => 1, 'required' => 'true']],
        'categories' => strval($category1->id).','.strval($category2->id),
        'schools' => strval($school1->id).','.strval($school2->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');

    $this->assertDatabaseHas('forms', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
    ]);

    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category1->id,
    ]);

    $this->assertDatabaseHas('form_school_category', [
        'form_id' => Form::all()->first()->id,
        'school_category_id' => $category2->id,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school1->id,
    ]);

    $this->assertDatabaseHas('form_school', [
        'form_id' => Form::all()->first()->id,
        'school_id' => $school2->id,
    ]);
});

it('cannot edit a form as user', function () {
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create();

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/edit')->assertForbidden();
});

it('can edit a form as author', function () {
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('can edit a form as admin', function () {
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('can edit a form with associated schools as author', function () {
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();
    $testForm->schools()->attach(School::factory()->for($author)->state(['name' => 'Test School1'])->create());
    $testForm->schools()->attach(School::factory()->for($author)->state(['name' => 'Test School2'])->create());

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('can edit a form with associated schools as admin', function () {
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();
    $testForm->schools()->attach(School::factory()->for($admin)->state(['name' => 'Test School1'])->create());
    $testForm->schools()->attach(School::factory()->for($admin)->state(['name' => 'Test School2'])->create());

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('can edit a form with associated school categories as author', function () {
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();
    $testForm->school_categories()->attach(SchoolCategory::factory()->state(['name' => 'Test Category1'])->create());
    $testForm->school_categories()->attach(SchoolCategory::factory()->state(['name' => 'Test Category2'])->create());

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('can edit a form with associated school categories as admin', function () {
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();
    $testForm->school_categories()->attach(SchoolCategory::factory()->state(['name' => 'Test Category1'])->create());
    $testForm->school_categories()->attach(SchoolCategory::factory()->state(['name' => 'Test Category2'])->create());

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('cannot delete a form as user', function () {
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create();

    $this->actingAs($user)->delete('/admin/form/'.$testForm->id)->assertForbidden();
});

it('can delete a form as author', function () {
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();

    $response = $this->actingAs($author)->delete('/admin/form/'.$testForm->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα διαγράφηκε');
});

it('can delete a form as admin', function () {
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();

    $response = $this->actingAs($admin)->delete('/admin/form/'.$testForm->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα διαγράφηκε');
});

it('cannot update a form as user', function () {
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create();

    $response = $this->actingAs($user)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form2',
        'field' => [
            [
                'title' => 'Test field',
                'type' => 0,
                'values' => '',
            ],
        ],
    ])->assertForbidden();
    $response->assertForbidden();
});

it('can update a form as author', function () {
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();

    $response = $this->actingAs($author)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form2',
        'field' => [
            [
                'title' => 'Test field',
                'type' => 0,
                'values' => '',
                'required' => 'true',
            ],
        ],
        'schools' => strval(School::factory()->for($author)->create(['name' => 'Test School'])->id).','.
            strval(School::factory()->for($author)->create(['name' => 'Test School2'])->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενημερώθηκε');
    expect(Form::find($testForm->id)->first()->title)->toBe('Test Form2');
    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);
    expect(Form::find($testForm->id)->first()->schools()->count())->toBe(2);

    $response = $this->actingAs($author)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form3',
        'field' => [
            [
                'title' => 'Test field2',
                'type' => 0,
                'values' => '',
                'required' => 'true',
            ],
            [
                'title' => 'Test field3',
                'type' => 1,
                'values' => '',
                'required' => 'false',
            ],
        ],
        'categories' => strval(SchoolCategory::factory()->create(['name' => 'Test Category'])->id).','.
            strval(SchoolCategory::factory()->create(['name' => 'Test Category2'])->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενημερώθηκε');
    expect(Form::find($testForm->id)->first()->title)->toBe('Test Form3');
    expect(Form::find($testForm->id)->first()->school_categories()->count())->toBe(2);
    $this->assertDatabaseMissing('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);
    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field2',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);
    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field3',
        'type' => 1,
        'listvalues' => '',
        'required' => 0,
    ]);
});

it('can update a form as admin', function () {
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();

    $response = $this->actingAs($admin)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form2',
        'field' => [
            [
                'title' => 'Test field',
                'type' => 0,
                'values' => '',
                'required' => 'true',
            ],
        ],
        'schools' => strval(School::factory()->for($admin)->create(['name' => 'Test School'])->id).','.
            strval(School::factory()->for($admin)->create(['name' => 'Test School2'])->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενημερώθηκε');
    expect(Form::find($testForm->id)->first()->title)->toBe('Test Form2');
    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);
    expect(Form::find($testForm->id)->first()->schools()->count())->toBe(2);

    $response = $this->actingAs($admin)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form3',
        'field' => [
            [
                'title' => 'Test field2',
                'type' => 0,
                'values' => '',
                'required' => 'true',
            ],
            [
                'title' => 'Test field3',
                'type' => 1,
                'values' => '',
                'required' => 'false',
            ],
        ],
        'categories' => strval(SchoolCategory::factory()->create(['name' => 'Test Category'])->id).','.
            strval(SchoolCategory::factory()->create(['name' => 'Test Category2'])->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενημερώθηκε');
    expect(Form::find($testForm->id)->first()->title)->toBe('Test Form3');
    expect(Form::find($testForm->id)->first()->schools()->count())->toBe(0);
    expect(Form::find($testForm->id)->first()->school_categories()->count())->toBe(2);
    $this->assertDatabaseMissing('form_fields', [
        'title' => 'Test field',
        'type' => 0,
        'listvalues' => '',
    ]);
    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field2',
        'type' => 0,
        'listvalues' => '',
        'required' => 1,
    ]);
    $this->assertDatabaseHas('form_fields', [
        'title' => 'Test field3',
        'type' => 1,
        'listvalues' => '',
        'required' => 0,
    ]);
});

it('can access a form\'s data as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/data')->assertOk();
});

it('can access a form\'s data as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/data')->assertOk();
});

it('can access a form\'s data as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);

    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $response = $this->actingAs($admin)->call('GET', '/admin/form/'.$testForm->id.'/data', ['noPagination' => 1])->assertOk();
    $field_data = FormFieldData::all();
    $response->assertViewIs('admin.form.data');
    foreach ($field_data as $data) {
        $response->assertSeeText($data->dataToString());
    }
});

it('can access a form\'s data (csv) as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/data/csv')->assertOk()->assertDownload();
});

it('can access a form\'s data (csv) as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/data/csv')->assertOk()->assertDownload();
});

it('can access a form\'s data (csv) as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/data/csv')->assertOk()->assertDownload();
});

it('can access a form\'s data (xlsx) as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/data/xlsx')->assertOk()->assertDownload();
});

it('can access a form\'s data (xlsx) as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/data/xlsx')->assertOk()->assertDownload();
});

it('can access a form\'s data (xlsx) as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/data/xlsx')->assertOk()->assertDownload();
});

it('can access a form\'s missing data as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/missing')->assertOk();
});

it('can access a form\'s missing data as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/missing')->assertOk();
});

it('can access a form\'s data missing as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/missing')->assertOk();
});

it('can access a form\'s missing data (csv) as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/missing/csv')->assertOk()->assertDownload();
});

it('can access a form\'s missing data (csv) as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/missing/csv')->assertOk()->assertDownload();
});

it('can access a form\'s data missing (csv) as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/missing/csv')->assertOk()->assertDownload();
});

it('can access a form\'s missing data (xlsx) as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/missing/xlsx')->assertOk()->assertDownload();
});

it('can access a form\'s missing data (xlsx) as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/missing/xlsx')->assertOk()->assertDownload();
});

it('can access a form\'s data missing (xlsx) as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/missing/xlsx')->assertOk()->assertDownload();
});

it('cannot copy a form as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $response = $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/copy');
    $response->assertForbidden();
});

it('can copy a form as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $response = $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/copy');
    $response->assertRedirect(route('admin.form.index'));
    expect($response->getSession()->only(['status'])['status'])->toBe('Το αντίγραφο της φόρμας δημιουργήθηκε');
    $this->assertModelExists($testForm);
});

it('can copy a form as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    $response = $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/copy');
    $response->assertRedirect(route('admin.form.index'));
    expect($response->getSession()->only(['status'])['status'])->toBe('Το αντίγραφο της φόρμας δημιουργήθηκε');
    $this->assertModelExists($testForm);
});

it('cannot change active state of a form as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);
    $testForm->active = 1;
    $testForm->save();

    $response = $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/active/set/0');
    $response->assertForbidden();

    $testForm->active = 0;
    $testForm->save();

    $response = $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/active/set/1');
    $response->assertForbidden();

    $response = $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/active/toggle');
    $response->assertForbidden();
});

it('can change active state of a form as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);
    $testForm->active = 1;
    $testForm->save();
    $tmpForm = $testForm->toArray();
    unset($tmpForm['updated_at']);
    unset($tmpForm['created_at']);
    $tmpForm['multiple'] = $tmpForm['multiple'] ? 1 : 0;

    $response = $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/active/set/0');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα απενεργοποιήθηκε');
    $tmpForm['active'] = 0;
    $this->assertDatabaseHas('forms', $tmpForm);

    $response = $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/active/set/1');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενεργοποιήθηκε');
    $tmpForm['active'] = 1;
    $this->assertDatabaseHas('forms', $tmpForm);

    $response = $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/active/toggle');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα απενεργοποιήθηκε');
    $tmpForm['active'] = 0;
    $this->assertDatabaseHas('forms', $tmpForm);

    $response = $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/active/toggle');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενεργοποιήθηκε');
    $tmpForm['active'] = 1;
    $this->assertDatabaseHas('forms', $tmpForm);
});

it('can change active state of a form as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);
    $testForm->active = 1;
    $testForm->save();
    $tmpForm = $testForm->toArray();
    unset($tmpForm['updated_at']);
    unset($tmpForm['created_at']);
    $tmpForm['multiple'] = $tmpForm['multiple'] ? 1 : 0;

    $response = $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/active/set/0');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα απενεργοποιήθηκε');
    $tmpForm['active'] = 0;
    $this->assertDatabaseHas('forms', $tmpForm);

    $response = $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/active/set/1');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενεργοποιήθηκε');
    $tmpForm['active'] = 1;
    $this->assertDatabaseHas('forms', $tmpForm);

    $response = $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/active/toggle');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα απενεργοποιήθηκε');
    $tmpForm['active'] = 0;
    $this->assertDatabaseHas('forms', $tmpForm);

    $response = $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/active/toggle');
    $response->assertRedirect(route('admin.form.index'));

    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενεργοποιήθηκε');
    $tmpForm['active'] = 1;
    $this->assertDatabaseHas('forms', $tmpForm);
});

it('can download a file attached to an answer in a form as user', function () {
    $user = User::factory()->user()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($user);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    // Βρες το πεδίο με τον τύπο αρχείο
    $field = FormField::query()->where('type', FormField::TYPE_FILE)->first();
    expect($field)->not->toBeNull();

    // Βρες τα δεδομένα που αποθηκεύτηκαν στο συγκεκριμένο πεδίο
    $data = FormFieldData::query()
        ->where('form_field_id', $field->id)
        ->first();
    expect($data)->not->toBeNull();

    $subfolder = 'school';
    $subfolderId = $data->school_id;
    if ($data->teacher_id) {
        $subfolder = 'teacher';
        $subfolderId = $data->teacher_id;
    } elseif ($data->other_teacher_id) {
        $subfolder = 'other_teacher';
        $subfolderId = $data->other_teacher_id;
    }

    // Αποθηκεύεις το αρχείο που περιέχει τα δεδομένα
    Storage::fake('local');
    Storage::put("report/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}", 'Test file content');

    $response = $this->actingAs($user)->get("/admin/download/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}");
    $response->assertDownload();
    expect($response->streamedContent())->toBe('Test file content');
});

it('can download a file attached to an answer in a form as author', function () {
    $author = User::factory()->author()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($author);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    // Βρες το πεδίο με τον τύπο αρχείο
    $field = FormField::query()->where('type', FormField::TYPE_FILE)->first();
    expect($field)->not->toBeNull();

    // Βρες τα δεδομένα που αποθηκεύτηκαν στο συγκεκριμένο πεδίο
    $data = FormFieldData::query()
        ->where('form_field_id', $field->id)
        ->first();
    expect($data)->not->toBeNull();

    $subfolder = 'school';
    $subfolderId = $data->school_id;
    if ($data->teacher_id) {
        $subfolder = 'teacher';
        $subfolderId = $data->teacher_id;
    } elseif ($data->other_teacher_id) {
        $subfolder = 'other_teacher';
        $subfolderId = $data->other_teacher_id;
    }

    // Αποθηκεύεις το αρχείο που περιέχει τα δεδομένα
    Storage::fake('local');
    Storage::put("report/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}", 'Test file content');

    $response = $this->actingAs($author)->get("/admin/download/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}");
    $response->assertDownload();
    expect($response->streamedContent())->toBe('Test file content');
});

it('can download a file attached to an answer in a form as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    // Βρες το πεδίο με τον τύπο αρχείο
    $field = FormField::query()->where('type', FormField::TYPE_FILE)->first();
    expect($field)->not->toBeNull();

    // Βρες τα δεδομένα που αποθηκεύτηκαν στο συγκεκριμένο πεδίο
    $data = FormFieldData::query()
        ->where('form_field_id', $field->id)
        ->first();
    expect($data)->not->toBeNull();

    $subfolder = 'school';
    $subfolderId = $data->school_id;
    if ($data->teacher_id) {
        $subfolder = 'teacher';
        $subfolderId = $data->teacher_id;
    } elseif ($data->other_teacher_id) {
        $subfolder = 'other_teacher';
        $subfolderId = $data->other_teacher_id;
    }

    // Αποθηκεύεις το αρχείο που περιέχει τα δεδομένα
    Storage::fake('local');
    Storage::put("report/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}", 'Test file content');

    $response = $this->actingAs($admin)->get("/admin/download/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}");
    $response->assertDownload();
    expect($response->streamedContent())->toBe('Test file content');
});

it('cannot download a file attached to an answer in a form as guest', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    // Βρες το πεδίο με τον τύπο αρχείο
    $field = FormField::query()->where('type', FormField::TYPE_FILE)->first();
    expect($field)->not->toBeNull();

    // Βρες τα δεδομένα που αποθηκεύτηκαν στο συγκεκριμένο πεδίο
    $data = FormFieldData::query()
        ->where('form_field_id', $field->id)
        ->first();
    expect($data)->not->toBeNull();

    $subfolder = 'school';
    $subfolderId = $data->school_id;
    if ($data->teacher_id) {
        $subfolder = 'teacher';
        $subfolderId = $data->teacher_id;
    } elseif ($data->other_teacher_id) {
        $subfolder = 'other_teacher';
        $subfolderId = $data->other_teacher_id;
    }

    // Αποθηκεύεις το αρχείο που περιέχει τα δεδομένα
    Storage::fake('local');
    Storage::put("report/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}", 'Test file content');

    $this->get("/admin/download/{$testForm->id}/{$subfolder}/{$subfolderId}/{$data->record}/{$field->id}")
        ->assertRedirect('/admin/login');
});

it('cannot download all files attached to a form as admin', function () {
    $admin = User::factory()->admin()->create();
    $this->seed([RoleSeeder::class, UserSeeder::class, SchoolCategorySeeder::class, SchoolSeeder::class]);
    $testForm = test_create_one_form_for_user($admin);
    $this->seed(FormFieldDataSeeder::class);
    $this->assertInstanceOf(Form::class, $testForm);

    // Βρες το πεδίο με τον τύπο αρχείο
    $field = FormField::query()->where('type', FormField::TYPE_FILE)->first();
    expect($field)->not->toBeNull();

    // Βρες τα δεδομένα που αποθηκεύτηκαν στο συγκεκριμένο πεδίο
    $data = FormFieldData::query()
        ->where('form_field_id', $field->id)
        ->get();
    expect($data)->not->toBeNull();

    // Αποθηκεύεις το αρχείο που περιέχει τα δεδομένα
    foreach ($data as $item) {
        $subfolder = 'school';
        $subfolderId = $item->school_id;
        if ($item->teacher_id) {
            $subfolder = 'teacher';
            $subfolderId = $item->teacher_id;
        } elseif ($item->other_teacher_id) {
            $subfolder = 'other_teacher';
            $subfolderId = $item->other_teacher_id;
        }

        Storage::put("report/{$testForm->id}/{$subfolder}/{$subfolderId}/{$item->record}/{$field->id}", 'Test file content');
    }

    /** @var Illuminate\Testing\TestResponse $response */
    $response = $this->actingAs($admin)->get("/admin/download_all/{$testForm->id}");
    $response->assertDownload();

    // Έλεγξε ότι έχουμε τόσα αρχεία όσα δημιουργήσαμε
    $tmp_filename = tempnam(sys_get_temp_dir(), 'zip');
    $tmp = fopen($tmp_filename, 'w');
    fwrite($tmp, $response->streamedContent());
    fclose($tmp);
    $archive = new ZipArchive;
    $archive->open($tmp_filename);
    expect($archive->count())->toBe(count($data));
    $archive->close();

    // Εκκαθάριση προσωρινών αρχείων και φακέλων
    unlink($tmp_filename);
    Storage::deleteDirectory("report/{$testForm->id}");
});
