<?php

use App\Models\Option;
use App\Models\User;
use Database\Seeders\OptionSeeder;

beforeEach(function () {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();
});

it('cannot access the other teacher panel as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/other_teacher')->assertForbidden();
});

it('cannot access the other teacher panel as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/other_teacher')->assertForbidden();
});

it('can access the other teacher panel as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/other_teacher')->assertOk();
});
