<?php

use App\Models\Option;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Illuminate\Support\Facades\Route;
use Subfission\Cas\Facades\Cas;

class TestCasManager {

}

beforeEach(function() {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();

    $this->app->singleton('cas', function () {
        return new TestCasManager();
    });

    // Cas::shouldReceive('checkAuthentication')
    //     ->andReturn(NULL);
    // Cas::shouldReceive('authenticate')
    //     ->andReturn(NULL);
    // Cas::shouldReceive('getAttribute')
    //     ->andReturn(NULL);
    Cas::shouldReceive('isAuthenticated')
        ->andReturn(NULL);
    Cas::shouldReceive('user')
        ->andReturn(NULL);
    Cas::shouldReceive('logout')
        ->andReturn(NULL);
    Cas::shouldReceive('client')
        ->andReturn(NULL);
});

it('can get /', function() {
    $response = $this->get('/');

    $response->assertOk();
});

it('shows first run setup', function() {
    $option = Option::where('name', 'first_run')->first();
    $option->value = 1;
    $option->save();
    $response = $this->get('/');

    $response->assertRedirect('/setup');
});

it('cannot get /setup after first run setup', function() {
    $response = $this->get('/setup');

    $response->assertRedirect('/');
});

it('gets /admin/login without logging in', function($url) {
    $response = $this->get($url);

    $response->assertRedirect('/admin/login');

})->with('admin_routes');

it('gets cas/login without logging in', function($url) {

    Cas::shouldReceive('checkAuthentication')
        ->andReturnFalse();
    Cas::shouldReceive('authenticate')
        ->andThrow(Exception::class,"Must authenticate with CAS");

    $response = $this->get('/report');
    $response->assertStatus(500);
    expect($response->baseResponse->exception->getMessage())->toBe('Must authenticate with CAS');
})->with('sch_routes');

it('gets cas/login logged in as user', function($url) {

    Cas::shouldReceive('checkAuthentication')
        ->andReturnFalse();
    Cas::shouldReceive('authenticate')
        ->andThrow(Exception::class,"Must authenticate with CAS");

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

it('can access the admin backend as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin')->assertOk();
});

it('can access the admin backend as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin')->assertOk();
});

it('can access the admin backend as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('cannot access the users panel as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/user')->assertForbidden();
});

it('cannot access the users panel as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/user')->assertForbidden();
});

it('can access the users panel as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/user')->assertOk();
});

it('cannot access another user\'s info as user', function() {
    $user = User::factory()->user()->create();
    $testUser = User::factory()->create();

    $this->actingAs($user)->get('/admin/user/'.$testUser->id)->assertForbidden();
});

it('cannot access another user\'s info as author', function() {
    $author = User::factory()->author()->create();
    $testUser = User::factory()->create();

    $this->actingAs($author)->get('/admin/user/'.$testUser->id)->assertForbidden();
});

it('can access another user\'s info as admin', function() {
    $admin = User::factory()->admin()->create();
    $testUser = User::factory()->create();

    $this->actingAs($admin)->get('/admin/user/'.$testUser->id)->assertOk();
});

it('can access it\'s own user info as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/user/'.$user->id)->assertOk();
});

it('can access it\'s own user info as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/user/'.$author->id)->assertOk();
});

it('can access it\'s own user info as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/user/'.$admin->id)->assertOk();
});

it('cannot create another user as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/user/create')->assertForbidden();
    $this->actingAs($user)->post('/admin/user', [
        'name' => 'Test User',
        'email' => 'test@test.com',
        'username' => 'testUser',
        'password' => 'mySecretPassword',
        'password_confirmation' => 'mySecretPassword'
    ])->assertForbidden();
});

it('cannot create another user as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/user/create')->assertForbidden();
    $this->actingAs($author)->post('/admin/user', [
        'name' => 'Test User',
        'email' => 'test@test.com',
        'username' => 'testUser',
        'password' => 'mySecretPassword',
        'password_confirmation' => 'mySecretPassword'
    ])->assertForbidden();
});

it('can create another user as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/user/create')->assertOk();
    $response = $this->actingAs($admin)->post('/admin/user', [
        'name' => 'Test User',
        'email' => 'test@test.com',
        'username' => 'testUser',
        'password' => 'mySecretPassword',
        'password_confirmation' => 'mySecretPassword'
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User saved!');
});

it('cannot edit another user\'s info as user', function() {
    $user = User::factory()->user()->create();
    $testUser = User::factory()->create();

    $this->actingAs($user)->get('/admin/user/'.$testUser->id.'/edit')->assertForbidden();
});

it('cannot edit another user\'s info as author', function() {
    $author = User::factory()->author()->create();
    $testUser = User::factory()->create();

    $this->actingAs($author)->get('/admin/user/'.$testUser->id.'/edit')->assertForbidden();
});

it('can edit another user\'s info as admin', function() {
    $admin = User::factory()->admin()->create();
    $testUser = User::factory()->create();

    $this->actingAs($admin)->get('/admin/user/'.$testUser->id.'/edit')->assertOk();
});

it('can edit it\'s own user info as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/user/'.$user->id.'/edit')->assertOk();
});

it('can edit it\'s own user info as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/user/'.$author->id.'/edit')->assertOk();
});

it('can edit it\'s own user info as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/user/'.$admin->id.'/edit')->assertOk();
});

it('cannot edit another user\'s password as user', function() {
    $user = User::factory()->user()->create();
    $testUser = User::factory()->create();

    $this->actingAs($user)->get('/admin/user/'.$testUser->id.'/password')->assertForbidden();
});

it('cannot edit another user\'s password as author', function() {
    $author = User::factory()->author()->create();
    $testUser = User::factory()->create();

    $this->actingAs($author)->get('/admin/user/'.$testUser->id.'/password')->assertForbidden();
});

it('can edit another user\'s password as admin', function() {
    $admin = User::factory()->admin()->create();
    $testUser = User::factory()->create();

    $this->actingAs($admin)->get('/admin/user/'.$testUser->id.'/password')->assertOk();
});

it('can edit it\'s own password as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/user/'.$user->id.'/password')->assertOk();
});

it('can edit it\'s own password as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/user/'.$author->id.'/password')->assertOk();
});

it('can edit it\'s own password as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/user/'.$admin->id.'/password')->assertOk();
});

it('cannot delete another user as user', function() {
    $user = User::factory()->user()->create();
    $testUser = User::factory()->create();

    $this->actingAs($user)->delete('/admin/user/'.$testUser->id)->assertForbidden();
});

it('cannot delete another user as author', function() {
    $author = User::factory()->author()->create();
    $testUser = User::factory()->create();

    $this->actingAs($author)->delete('/admin/user/'.$testUser->id)->assertForbidden();
});

it('can delete another user as admin', function() {
    $admin = User::factory()->admin()->create();
    $testUser = User::factory()->create();

    $response = $this->actingAs($admin)->delete('/admin/user/'.$testUser->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User deleted!');
});

it('cannot delete it\' own user as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->delete('/admin/user/'.$user->id)->assertForbidden();
});

it('cannot delete it\' own user as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->delete('/admin/user/'.$author->id)->assertForbidden();
});

it('can delete it\' own user as admin', function() {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->delete('/admin/user/'.$admin->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User deleted!');
});

it('cannot update another user\'s data as user', function() {
    $user = User::factory()->user()->create();
    $testUser = User::factory()->create();

    $this->actingAs($user)->put('/admin/user/'.$testUser->id, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testUser'
    ])->assertForbidden();
});

it('cannot update another user\'s data as author', function() {
    $author = User::factory()->author()->create();
    $testUser = User::factory()->create();

    $this->actingAs($author)->put('/admin/user/'.$testUser->id, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testUser'
    ])->assertForbidden();
});

it('can update another user\'s data as admin', function() {
    $admin = User::factory()->admin()->create();
    $testUser = User::factory()->create();

    $response = $this->actingAs($admin)->put('/admin/user/'.$testUser->id, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testUser'
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User updated!');
});

it('can update it\' own user data as user', function() {
    $user = User::factory()->user()->create();

    $response = $this->actingAs($user)->put('/admin/user/'.$user->id, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testUser'
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User updated!');
});

it('can update it\' own user data as author', function() {
    $author = User::factory()->author()->create();

    $response = $this->actingAs($author)->put('/admin/user/'.$author->id, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testUser'
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User updated!');
});

it('can update it\' own user data data as admin', function() {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->put('/admin/user/'.$admin->id, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testUser'
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User updated!');
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

it('cannot create a school as user', function(){
    $user = User::factory()->user()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $this->actingAs($user)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => strval($category->id),
    ])->assertForbidden();
});

it('cannot create a school as author', function(){
    $author = User::factory()->author()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $this->actingAs($author)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => strval($category->id),
    ])->assertForbidden();
});

it('can create a school as admin', function(){
    $admin = User::factory()->admin()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($admin)->post('/admin/school', [
        'name' => "Test School",
        'email' => "test@example.com",
        'username' => "testSchool",
        'code' => "9999999",
        'category' => strval($category->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα αποθηκεύτηκε!');
});

it('cannot access a school as user', function(){
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $this->actingAs($user)->get('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('cannot access a school as author', function(){
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $this->actingAs($author)->get('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('can access a school as admin', function(){
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $this->actingAs($admin)->get('/admin/school/'.$testSchool->id)->assertOk();
});

it('cannot edit a school as user', function(){
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $this->actingAs($user)->get('/admin/school/'.$testSchool->id.'/edit')->assertForbidden();
});

it('cannot edit a school as author', function(){
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $this->actingAs($author)->get('/admin/school/'.$testSchool->id.'/edit')->assertForbidden();
});

it('can edit a school as admin', function(){
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $this->actingAs($admin)->get('/admin/school/'.$testSchool->id.'/edit')->assertOk();
});

it('cannot delete a school as user', function(){
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $this->actingAs($user)->delete('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('cannot delete a school as author', function(){
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $this->actingAs($author)->delete('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('can delete a school as admin', function(){
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $response = $this->actingAs($admin)->delete('/admin/school/'.$testSchool->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα διαγράφηκε!');
});

it('cannot update a school as user', function(){
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($user)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school as author', function(){
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($author)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
    $response->assertForbidden();
});

it('can update a school as admin', function(){
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($admin)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα ενημερώθηκε!');
});
