<?php

use App\Models\Option;
use App\Models\User;
use Database\Seeders\TestDataSeeder;
use Laravel\Dusk\Browser;
use function Pest\Faker\faker;

beforeEach(function () {
    // Το πρώτο seed φορτώνει μόνο τις αρχικές τιμές για την λειτουργία της
    // εφαρμογής. Χρειαζόμαστε και το δεύτερο για να παραχθούν επιπλέον δοκιμαστικά
    // δεδομένα.
    $this->seed();
    $this->seed(TestDataSeeder::class);

    $first_run = Option::where('name', 'first_run')->first();
    $first_run->value=0;
    $first_run->save();
});

it('cannot create/edit/delete forms as user', function () {
    $user = User::whereHas('roles', function ($query) {
        $query->where('name', 'User');
    })->first();
    $this->actingAs($user)->browse(function (Browser $browser) {
        $browser->visit('/admin/form')
            ->assertDontSee('Διαγραφή')
            ->assertDontSee('Επεξεργασία')
            ->assertDontSee('Δημιουργία φόρμας');
    });
});
