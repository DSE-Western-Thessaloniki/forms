<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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
    ])
    ->toBeFinal()
    ->ignoring([
        Controller::class,
        "App\Http\Traits",
    ]);

arch()
    ->expect('App\Models')
    ->toExtend(Model::class)
    ->ignoring([
        User::class,
        "App\Models\Policies",
    ]);

arch()
    ->expect('App\Contracts')
    ->toBeInterfaces();

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();
