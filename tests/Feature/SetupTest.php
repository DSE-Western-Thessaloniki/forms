<?php

use Database\Seeders\OptionSeeder;

it('validates setup form', function (array $setup, $empty): void {
    $this->seed(OptionSeeder::class);

    $response = $this->post(route('setup'), $setup);

    if (is_array($setup['errors'])) {
        $response->assertSessionHasErrors($setup['errors']);
    } else {
        $response->assertSessionHasNoErrors();
    }
})->with('setup_validation_data');
