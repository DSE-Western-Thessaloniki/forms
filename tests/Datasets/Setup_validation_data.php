<?php

dataset('setup_validation_data', function () {
    return [
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '123456789',
                'redirection' => 'setup',
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'redirection' => 'index',
            ]
        ],
    ];
});
