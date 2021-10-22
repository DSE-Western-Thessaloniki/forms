<?php

dataset('setup_validation_data', function () {
    return [
        [
            'setup' => [
                'name' => '',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => ['name'],
            ]
        ],
        [
            'setup' => [
                'name' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => ['name'],
            ]
        ],
        [
            'setup' => [
                'name' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => 0,
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaa.aa',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => ['email'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aa.aa',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => 0,
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => '',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => ['email'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '123456789',
                'errors' => ['email'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example@com',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '123456789',
                'errors' => ['email'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => '',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => ['username'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => ['username'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => 0,
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '',
                'password_confirmation' => '',
                'errors' => ['password'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '1234567',
                'password_confirmation' => '1234567',
                'errors' => ['password'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => 0,
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'password_confirmation' => '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'errors' => ['password'],
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'password_confirmation' => '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
                'errors' => 0,
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation@example.com',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'errors' => 0,
            ]
        ],
        [
            'setup' => [
                'name' => 'setup_validation_datum A',
                'email' => 'setup_validation',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '123456789',
                'errors' => ['email', 'password'],
            ]
        ],
        [
            'setup' => [
                'name' => '',
                'email' => 'setup_validation',
                'username' => 'setup_',
                'password' => '12345678',
                'password_confirmation' => '123456789',
                'errors' => ['name', 'email', 'password'],
            ]
        ],
        [
            'setup' => [
                'name' => '',
                'email' => 'setup_validation',
                'username' => '',
                'password' => '12345678',
                'password_confirmation' => '123456789',
                'errors' => ['name', 'email', 'username', 'password'],
            ]
        ],
    ];
});
