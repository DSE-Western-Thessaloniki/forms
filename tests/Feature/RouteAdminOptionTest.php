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

it('cannot access the options panel as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/options')->assertForbidden();
});

it('cannot access the options panel as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/options')->assertForbidden();
});

it('can access the options panel as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/options')->assertOk();
});

it('cannot store options as user', function () {
    $user = User::factory()->user()->create();
    $options = [
        'allow_teacher_login' => '1',
        'allow_all_teachers' => '1',
    ];

    $this->actingAs($user)->post('/admin/options', $options)->assertForbidden();
});

it('cannot store options as author', function () {
    $author = User::factory()->author()->create();
    $options = [
        'allow_teacher_login' => '1',
        'allow_all_teachers' => '1',
    ];

    $this->actingAs($author)->post('/admin/options', $options)->assertForbidden();
});

it('can store options as admin', function () {
    $admin = User::factory()->admin()->create();
    $options = [
        'allow_teacher_login' => '1',
        'allow_all_teachers' => '1',
    ];

    $response = $this->actingAs($admin)->post('/admin/options', $options)->assertRedirect('/admin/options');
    $response->assertSessionHas('status', 'Οι ρυθμίσεις αποθηκεύτηκαν!');
    expect(Option::where('name', 'allow_teacher_login')->first()->value)->toBe('1');
    expect(Option::where('name', 'allow_all_teachers')->first()->value)->toBe('1');
});

it('can store options as admin (don\'t allow all teachers)', function () {
    $admin = User::factory()->admin()->create();
    $options = [
        'allow_teacher_login' => '1',
        'allow_all_teachers' => '0',
    ];

    $response = $this->actingAs($admin)->post('/admin/options', $options)->assertRedirect('/admin/options');
    $response->assertSessionHas('status', 'Οι ρυθμίσεις αποθηκεύτηκαν!');
    expect(Option::where('name', 'allow_teacher_login')->first()->value)->toBe('1');
    expect(Option::where('name', 'allow_all_teachers')->first()->value)->toBe('0');
});

it('can store options as admin (don\'t allow teachers)', function () {
    $admin = User::factory()->admin()->create();
    $options = [
        'allow_teacher_login' => '0',
        'allow_all_teachers' => '0',
    ];

    $response = $this->actingAs($admin)->post('/admin/options', $options)->assertRedirect('/admin/options');
    $response->assertSessionHas('status', 'Οι ρυθμίσεις αποθηκεύτηκαν!');
    expect(Option::where('name', 'allow_teacher_login')->first()->value)->toBe('0');
    // Η τιμή είναι 1 γιατί είναι το default
    expect(Option::where('name', 'allow_all_teachers')->first()->value)->toBe('1');
});
