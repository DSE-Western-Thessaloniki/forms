<?php

use App\Models\Option;
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
