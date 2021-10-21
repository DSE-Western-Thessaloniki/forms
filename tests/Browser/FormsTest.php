<?php

use App\Models\Form;
use App\Models\Option;
use App\Models\User;
use Database\Seeders\TestDataSeeder;
use Database\Seeders\UserSeeder;
use Laravel\Dusk\Browser;
use function Pest\Faker\faker;

beforeEach(function () {
    // Το πρώτο seed φορτώνει μόνο τις αρχικές τιμές για την λειτουργία της
    // εφαρμογής. Χρειαζόμαστε και το δεύτερο για να παραχθούν επιπλέον δοκιμαστικά
    // δεδομένα.
    $this->seed();
    $this->seed(UserSeeder::class);

    $first_run = Option::where('name', 'first_run')->first();
    $first_run->value=0;
    $first_run->save();
});

it('cannot create/edit/delete forms as user', function () {
    Form::factory()->for(User::where('username', 'admin0')->first())->create();

    $user = User::whereHas('roles', function ($query) {
        $query->where('name', 'User');
    })->first();
    $user->active = true;
    $user->save();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)->visit('/admin/form')
            ->assertSeeIn('div.card-header', 'Φόρμες')
            ->assertDontSeeIn('button[type="submit"]', 'Διαγραφή')
            ->assertDontSeeLink('Επεξεργασία')
            ->assertDontSeeLink('Δημιουργία φόρμας');
    });
});

it('can create/edit/delete forms as author', function () {
    $author = User::whereHas('roles', function ($query) {
        $query->where('name', 'Author');
    })->first();
    $author->active = true;
    $author->save();

    Form::factory()->for($author)->create();

    $this->browse(function (Browser $browser) use ($author) {
        $browser->loginAs($author)->visit('/admin/form')
            ->assertSeeIn('div.card-header', 'Φόρμες')
            ->assertSeeIn('button[type="submit"]', 'Διαγραφή')
            ->assertSeeLink('Επεξεργασία')
            ->assertSeeLink('Δημιουργία φόρμας');
    });

});

it('can edit/delete only forms created by author', function () {
    $author = User::whereHas('roles', function ($query) {
        $query->where('name', 'Author');
    })->first();
    $author->active = true;
    $author->save();

    Form::factory()->for(User::where('username', 'admin0')->first())->create();

    $this->browse(function (Browser $browser) use ($author) {
        $browser->loginAs($author)->visit('/admin/form')
            ->assertSeeIn('div.card-header', 'Φόρμες')
            ->assertDontSeeIn('button[type="submit"]', 'Διαγραφή')
            ->assertDontSeeLink('Επεξεργασία')
            ->assertSeeLink('Δημιουργία φόρμας');
    });

});
