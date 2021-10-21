<?php

use App\Models\Option;
use App\Models\School;
use App\Models\User;
use Database\Seeders\OptionSeeder;
//use Subfission\Cas\Facades\Cas;
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


    School::factory()->for(User::factory())->create([
        'name' => 'Test School',
        'username' => '999',
    ]);

    $this->get('/report')
        ->assertDontSee('Σφάλμα')
        ->assertSee('Δεν βρέθηκαν φόρμες');
});
