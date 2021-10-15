<?php

use App\Models\Option;
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

it('can access the users panel as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/user')->assertForbidden();
});

it('can access the users panel as author', function() {
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

    $this->actingAs($user)->get('/admin/user/'.$testUser->id)->assertForbidden();
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

it('cannot delete another it\' own user as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/user/'.$user->id)->assertForbidden();
});

it('cannot delete another it\' own user as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->delete('/admin/user/'.$author->id)->assertForbidden();
});

it('can delete another it\' own user as admin', function() {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->delete('/admin/user/'.$admin->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('User deleted!');
});
