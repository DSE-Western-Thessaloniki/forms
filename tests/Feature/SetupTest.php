<?php

use Database\Seeders\OptionSeeder;

it('validates setup form', function($setup) {
    $this->seed(OptionSeeder::class);

    $response = $this->post(route('setup'), [
        'name' => $setup['name'],
        'email' => $setup['email'],
        'username' => $setup['username'],
        'password' => $setup['password'],
        'password_confirmation' => $setup['password_confirmation'],
    ]);

    if (is_array($setup['errors'])) {
        $response->assertSessionHasErrors($setup['errors']);
    }
    else {
        $response->assertSessionHasNoErrors();
    }
})->with('setup_validation_data');
