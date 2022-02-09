<?php

use App\Models\Option;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Database\Seeders\OptionSeeder;

beforeEach(function() {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();
});


it('cannot access the schools panel as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/school')->assertForbidden();
});

it('cannot access the schools panel as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/school')->assertForbidden();
});

it('can access the schools panel as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/school')->assertOk();
});

it('cannot access the school creation as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/school/create')->assertForbidden();
});

it('cannot access the school creation as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/school/create')->assertForbidden();
});

it('can access the school creation as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/school/create')->assertOk();
});

it('cannot access a school\'s info as user', function() {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $this->actingAs($user)->get('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('cannot access a school\'s info as author', function() {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $this->actingAs($author)->get('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('can access a school\'s info as admin', function() {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $this->actingAs($admin)->get('/admin/school/'.$testSchool->id)->assertOk();
});

it('cannot create a school as user', function() {
    $user = User::factory()->user()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $this->actingAs($user)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'telephone' => "123-456-7890",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => strval($category->id),
    ])->assertForbidden();
});

it('cannot create a school as author', function() {
    $author = User::factory()->author()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $this->actingAs($author)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'telephone' => "123-456-7890",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => strval($category->id),
    ])->assertForbidden();
});

it('can create a school as admin', function() {
    $admin = User::factory()->admin()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($admin)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'telephone' => "123-456-7890",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => strval($category->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα αποθηκεύτηκε!');
});

it('cannot create a school as user with invalid categories', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'telephone' => "123-456-7890",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => "0,1",
    ])->assertForbidden();
});

it('cannot create a school as author with invalid categories', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'telephone' => "123-456-7890",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => "0,1",
    ])->assertForbidden();
});

it('cannot create a school as admin with invalid categories', function() {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'telephone' => "123-456-7890",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => "0,1",
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['status'])['status'])->toBe('Άκυρες κατηγορίες');
});

it('cannot edit a school as user', function() {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 1']));
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 2']));

    $this->actingAs($user)->get('/admin/school/'.$testSchool->id.'/edit')->assertForbidden();
});

it('cannot edit a school as author', function() {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 1']));
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 2']));

    $this->actingAs($author)->get('/admin/school/'.$testSchool->id.'/edit')->assertForbidden();
});

it('can edit a school as admin', function() {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 1']));
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 2']));

    $this->actingAs($admin)->get('/admin/school/'.$testSchool->id.'/edit')->assertOk();
});

it('cannot delete a school as user', function() {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $this->actingAs($user)->delete('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('cannot delete a school as author', function() {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $this->actingAs($author)->delete('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('can delete a school as admin', function() {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $response = $this->actingAs($admin)->delete('/admin/school/'.$testSchool->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα διαγράφηκε!');
});

it('cannot update a school as user', function() {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($user)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => "123-456-7890",
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school as author', function() {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($author)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => "123-456-7890",
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
    $response->assertForbidden();
});

it('can update a school as admin', function() {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($admin)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => "123-456-7890",
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα ενημερώθηκε!');
});

it('cannot update a school as user with invalid categories', function() {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $response = $this->actingAs($user)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => "123-456-7890",
        'username' => 'testUser',
        'code' => '9999999',
        'category' => "0,1",
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school as author with invalid categories', function() {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $response = $this->actingAs($author)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => "123-456-7890",
        'username' => 'testUser',
        'code' => '9999999',
        'category' => "0,1",
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school as admin with invalid categories', function() {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $response = $this->actingAs($admin)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => "123-456-7890",
        'username' => 'testUser',
        'code' => '9999999',
        'category' => "0,1",
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['status'])['status'])->toBe('Άκυρες κατηγορίες');
});
