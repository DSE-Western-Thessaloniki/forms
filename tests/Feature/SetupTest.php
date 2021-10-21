<?php

it('validates setup form', function($setup) {
    $response = $this->post('setup', [
        'name' => $setup['name'],
        'email' => $setup['email'],
        'username' => $setup['username'],
        'password' => $setup['password'],
        'password_confirmation' => $setup['password'],
    ]);
    //dd($response);
    $response->assertRedirect($setup['redirection']);
})->with('setup_validation_data');
