<?php

use App\Models\Option;
use Database\Seeders\OptionSeeder;
use Illuminate\Support\Facades\Route;

beforeEach(function() {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();
});

it('can get /', function() {
    $response = $this->get('/');

    $response->assertOk();
});

it('shows first run setup', function() {
    $option = Option::where('name', 'first_run')->first();
    $option->value = 1;
    $option->save();
    $response = $this->get('/');

    $response->assertRedirect('/setup');
});

it('cannot get /setup after first run setup', function() {
    $response = $this->get('/setup');

    $response->assertRedirect('/');
});

it('gets /admin/login without logging in', function($url) {
    $response = $this->get($url);

    $response->assertRedirect('/admin/login');

})->with('admin_routes');
