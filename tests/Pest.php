<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Subfission\Cas\Facades\Cas;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class, LazilyRefreshDatabase::class)->in('Feature');
uses(Tests\DuskTestCase::class, DatabaseMigrations::class)->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function test_cas_null()
{
    Cas::shouldReceive('isAuthenticated')
        ->andReturnNull();
    Cas::shouldReceive('user')
        ->andReturnNull();
    Cas::shouldReceive('logout')
        ->andReturnNull();
    Cas::shouldReceive('client')
        ->andReturnNull();
}

function test_cas_not_logged_in() {
    Cas::shouldReceive('checkAuthentication')
        ->andReturnFalse();
    Cas::shouldReceive('authenticate')
        ->andThrow(Exception::class,"Must authenticate with CAS");
}

function test_cas_logged_in() {
    Cas::shouldReceive('isAuthenticated')
        ->andReturnTrue();
    Cas::shouldReceive('checkAuthentication')
        ->andReturnTrue();
    Cas::shouldReceive('getAttribute')
        ->with('uid')
        ->andReturn('999');
    Cas::shouldReceive('getAttribute')
        ->with('mail')
        ->andReturn('tst@sch.gr');
    Cas::shouldReceive('getAttribute')
        ->with('cn')
        ->andReturn('Dokimastiki monada');
}
