<?php

use App\Models\Option;
use Database\Seeders\OptionSeeder;
use Laravel\Dusk\Browser;

beforeEach(function() {

});

// it('should be ok', function() {
//     $option = new Option();
//     $option->name = 'first_run';
//     $option->value = 0;
//     $option->save();
//     expect($option)->toBeInstanceOf('App\Models\Option');
// });

it('shows first run setup', function () {

    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Ρύθμιση διαχειριστή συστήματος');
    });
})->tap(fn() => $this->seed(OptionSeeder::class));
