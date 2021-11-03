<?php

use App\Models\Option;
use App\Models\User;
use Database\Seeders\OptionSeeder;
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

    $this->get('/setup')->assertOk();
});

it('cannot get /setup after first run setup', function() {
    $response = $this->get('/setup');

    $response->assertRedirect('/');
});

it('gets /admin/login without logging in', function($url) {
    $response = $this->get($url);

    $response->assertRedirect('/admin/login');

})->with('admin_routes');

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
