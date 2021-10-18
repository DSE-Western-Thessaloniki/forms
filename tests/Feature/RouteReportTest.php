<?php

use App\Models\Option;
use App\Models\User;
use Database\Seeders\OptionSeeder;
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
