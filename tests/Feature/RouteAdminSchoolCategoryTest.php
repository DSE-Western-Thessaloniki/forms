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


it('cannot access the school category panel as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/school/schoolcategory')->assertForbidden();
});

it('cannot access the school category panel as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/school/schoolcategory')->assertForbidden();
});

it('can access the school category panel as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/school/schoolcategory')->assertOk();
});

it('cannot access a school category\'s info as user', function() {
    $user = User::factory()->user()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => 'Test Category']);

    $this->actingAs($user)->get('/admin/school/schoolcategory/'.$testSchoolCategory->id)->assertForbidden();
});

it('cannot access a school category\'s info as author', function() {
    $author = User::factory()->author()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => 'Test Category']);

    $this->actingAs($author)->get('/admin/school/schoolcategory/'.$testSchoolCategory->id)->assertForbidden();
});

it('can access a school category\'s info as admin', function() {
    $admin = User::factory()->admin()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => 'Test Category']);

    $this->actingAs($admin)->get('/admin/school/schoolcategory/'.$testSchoolCategory->id)->assertOk();
});

it('cannot access a school category\'s creation form as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/school/schoolcategory/create')->assertForbidden();
});

it('cannot access a school category\'s creation form as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/school/schoolcategory/create')->assertForbidden();
});

it('can access a school category\'s creation form as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/school/schoolcategory/create')->assertOk();
});

it('cannot create a school category as user', function(){
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post('/admin/school/schoolcategory', [
        'name' => "Test Category",
    ])->assertForbidden();
});

it('cannot create a school category as author', function(){
    $author = User::factory()->author()->create();

    $this->actingAs($author)->post('/admin/school/schoolcategory', [
        'name' => "Test Category",
    ])->assertForbidden();
});

it('can create a school category as admin', function(){
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/school/schoolcategory', [
        'name' => "Test Category",
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η κατηγορία σχολικής μονάδας αποθηκεύτηκε!');
});

it('cannot edit a school category as user', function(){
    $user = User::factory()->user()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => "Test Category"]);

    $this->actingAs($user)->get('/admin/school/schoolcategory/'.$testSchoolCategory->id.'/edit')->assertForbidden();
});

it('cannot edit a school category as author', function(){
    $author = User::factory()->author()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => "Test Category"]);

    $this->actingAs($author)->get('/admin/school/schoolcategory/'.$testSchoolCategory->id.'/edit')->assertForbidden();
});

it('can edit a school category as admin', function(){
    $admin = User::factory()->admin()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => "Test Category"]);

    $this->actingAs($admin)->get('/admin/school/schoolcategory/'.$testSchoolCategory->id.'/edit')->assertOk();
});

it('cannot delete a school category as user', function(){
    $user = User::factory()->user()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => "Test Category"]);

    $this->actingAs($user)->delete('/admin/school/schoolcategory/'.$testSchoolCategory->id)->assertForbidden();
});

it('cannot delete a school category as author', function(){
    $author = User::factory()->author()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => "Test Category"]);

    $this->actingAs($author)->delete('/admin/school/schoolcategory/'.$testSchoolCategory->id)->assertForbidden();
});

it('can delete a school category as admin', function(){
    $admin = User::factory()->admin()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => "Test Category"]);

    $response = $this->actingAs($admin)->delete('/admin/school/schoolcategory/'.$testSchoolCategory->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η κατηγορία σχολικής μονάδας διαγράφηκε!');
});

it('cannot update a school category as user', function(){
    $user = User::factory()->user()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($user)->put('/admin/school/schoolcategory/'.$testSchoolCategory->id, [
        'name' => 'TestCategory2',
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school category as author', function(){
    $author = User::factory()->author()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($author)->put('/admin/school/schoolcategory/'.$testSchoolCategory->id, [
        'name' => 'TestCategory2',
    ])->assertForbidden();
    $response->assertForbidden();
});

it('can update a school category as admin', function(){
    $admin = User::factory()->admin()->create();
    $testSchoolCategory = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($admin)->put('/admin/school/schoolcategory/'.$testSchoolCategory->id, [
        'name' => 'TestCategory2',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η κατηγορία σχολικής μονάδας ενημερώθηκε!');
});
