<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
    Cas::shouldReceive('businesscategory')
        ->andReturnNull();
}

function test_cas_not_logged_in()
{
    Cas::shouldReceive('checkAuthentication')
        ->andReturnFalse();
    Cas::shouldReceive('authenticate')
        ->andThrow(Exception::class, 'Must authenticate with CAS');
}

function test_cas_logged_in(int $uid = 999, string $mail = 'tst@sch.gr', string $cn = 'Dokimastiki monada', string $businessCategory = '')
{
    Cas::shouldReceive('isAuthenticated')
        ->andReturnTrue();
    Cas::shouldReceive('checkAuthentication')
        ->andReturnTrue();
    Cas::shouldReceive('getAttribute')
        ->with('uid')
        ->andReturn("$uid");
    Cas::shouldReceive('getAttribute')
        ->with('mail')
        ->andReturn($mail);
    Cas::shouldReceive('getAttribute')
        ->with('cn')
        ->andReturn($cn);
    Cas::shouldReceive('getAttribute')
        ->with('employeenumber')
        ->andReturnNull();
    Cas::shouldReceive('getAttribute')
        ->with('businesscategory')
        ->andReturn($businessCategory);
}

function test_cas_logged_in_as_teacher(int $uid = 888, string $mail = 'tstteacher@sch.gr', string $cn = 'Dokimastikos ekpaideytikos', string $businessCategory = 'ΕΚΠΑΙΔΕΥΤΙΚΟΣ', string $employeeNumber = '123456')
{
    Cas::shouldReceive('isAuthenticated')
        ->andReturnTrue();
    Cas::shouldReceive('checkAuthentication')
        ->andReturnTrue();
    Cas::shouldReceive('getAttribute')
        ->with('uid')
        ->andReturn("$uid");
    Cas::shouldReceive('getAttribute')
        ->with('mail')
        ->andReturn($mail);
    Cas::shouldReceive('getAttribute')
        ->with('cn')
        ->andReturn($cn);
    Cas::shouldReceive('getAttribute')
        ->with('employeenumber')
        ->andReturn($employeeNumber);
    Cas::shouldReceive('getAttribute')
        ->with('businesscategory')
        ->andReturn($businessCategory);
}

function test_create_one_form_for_user(User $user): Form
{
    $form = Form::factory()
        ->for($user)
        ->has(
            FormField::factory()
                ->count(10)
                ->state(new Sequence(function ($sequence) {
                    $type = $sequence->index;

                    // Αν ο τύπος του πεδίου χρειάζεται επιπλέον επιλογές
                    if (in_array($type, [2, 3, 4])) {
                        $listvalues = [];
                        for ($i = 0; $i < rand(1, 10); $i++) {
                            array_push(
                                $listvalues,
                                [
                                    'id' => $i,
                                    'value' => 'Test',
                                ]
                            );
                        }

                        return [
                            'sort_id' => $sequence->index,
                            'type' => $type,
                            'listvalues' => json_encode($listvalues),
                        ];
                    } else {
                        return [
                            'sort_id' => $sequence->index,
                            'type' => $type,
                            'listvalues' => '',
                        ];
                    }
                })),
            'form_fields'
        )
        ->create();

    // Σύνδεση φόρμας με σχολική μονάδα και κατηγορία σχολείου
    $form->schools()->attach(School::inRandomOrder()->first()->id);
    $form->school_categories()->attach(SchoolCategory::inRandomOrder()->first()->id);

    return $form;
}
