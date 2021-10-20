<?php

use Database\Seeders\OptionSeeder;
use Laravel\Dusk\Browser;
use function Pest\Faker\faker;

it('shows first run setup', function () {
    $this->seed(OptionSeeder::class);

    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Ρύθμιση διαχειριστή συστήματος');
    });
});

it('completes first run setup', function () {
    $this->seed(OptionSeeder::class);

    $this->browse(function (Browser $browser) {
        $password = faker()->password();
        $browser->visit('/')
            ->assertSee('Ρύθμιση διαχειριστή συστήματος')
            ->type('name', faker()->name())
            ->type('email', faker()->email())
            ->type('username', faker()->username())
            ->type('password', $password)
            ->type('password_confirmation', $password)
            ->click('button[type="submit"]')
            ->waitForLocation('/home')
            ->assertPathIs('/home');
    });
});
