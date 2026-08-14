<?php

declare(strict_types=1);

dataset('setup_validation_data', fn (): array => [
    [
        'setup' => [
            'name' => '',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => ['name'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => ['name'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => 0,
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaa.aa',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => ['email'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aa.aa',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => 0,
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => '',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => ['email'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '123456789',
            'errors' => ['email'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example@com',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '123456789',
            'errors' => ['email'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => '',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => ['username'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => ['username'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => 0,
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '',
            'password_confirmation' => '',
            'errors' => ['password'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '1234567',
            'password_confirmation' => '1234567',
            'errors' => ['password'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => 0,
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'password_confirmation' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'errors' => ['password'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'password_confirmation' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'errors' => 0,
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation@example.com',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'errors' => 0,
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => 'setup_validation_datum A',
            'email' => 'setup_validation',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '123456789',
            'errors' => ['email', 'password'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => '',
            'email' => 'setup_validation',
            'username' => 'setup_',
            'password' => '12345678',
            'password_confirmation' => '123456789',
            'errors' => ['name', 'email', 'password'],
        ],
        'empty' => '',
    ],
    [
        'setup' => [
            'name' => '',
            'email' => 'setup_validation',
            'username' => '',
            'password' => '12345678',
            'password_confirmation' => '123456789',
            'errors' => ['name', 'email', 'username', 'password'],
        ],
        'empty' => '',
    ],
]);
