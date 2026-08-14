<?php

use Database\Seeders\OptionSeeder;
use Laravel\Dusk\Browser;

it('shows first run setup', function (): void {
    $this->seed(OptionSeeder::class);

    $this->browse(function (Browser $browser): void {
        $browser->visit('/')
            ->assertSee('Ρύθμιση διαχειριστή συστήματος');
    });
});

it('completes first run setup', function (): void {
    $this->seed(OptionSeeder::class);

    $this->browse(function (Browser $browser): void {
        $password = $this->faker()->password();
        $browser->visit('/')
            ->assertSee('Ρύθμιση διαχειριστή συστήματος')
            ->type('name', $this->faker()->name())
            ->type('email', $this->faker()->email())
            ->type('username', $this->faker()->username())
            ->type('password', $password)
            ->type('password_confirmation', $password)
            ->click('button[type="submit"]')
            ->waitForLocation('/home')
            ->assertPathIs('/home');
    });
});
