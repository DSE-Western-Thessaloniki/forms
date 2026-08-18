<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;

arch()
    ->expect('App')
    ->toUseStrictTypes()
    ->toBeCasedCorrectly()
    ->toUseStrictEquality()
    ->not->toUse(['die', 'dd', 'dump', 'sleep', 'usleep', 'exit', 'var_dump', 'print_r']);

arch()
    ->expect('App')
    ->toBeClasses()
    ->ignoring([
        "App\Http\Traits",
        "App\Traits",
    ])
    ->toBeFinal()
    ->ignoring([
        Controller::class,
        "App\Http\Traits",
        "App\Traits",
    ]);

arch()
    ->expect('App\Contracts')
    ->toBeInterfaces();

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();
