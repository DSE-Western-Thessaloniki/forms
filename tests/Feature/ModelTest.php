<?php

use App\Models\User;
use App\Models\Role;

it('can create a new user', function () {
    $user = User::factory()->create();

    $this->assertInstanceOf('App\Models\User', $user);
    $this->assertDatabaseHas('users', ['updated_by' => 0]);
});

it('can check for admin', function () {
    $user = User::factory()->admin()->create();

    $this->assertTrue($user->isAdministrator());
});
