<?php

use App\Models\AcceptedFiletype;
use App\Models\Option;
use App\Models\User;
use Database\Seeders\OptionSeeder;

beforeEach(function () {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();
});

it('cannot access the accepted filetype panel as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get(route('admin.accepted_filetype.index'))->assertForbidden();
});

it('cannot access the accepted filetype panel as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get(route('admin.accepted_filetype.index'))->assertForbidden();
});

it('can access the accepted filetype panel as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.accepted_filetype.index'))->assertOk();
});

it('cannot access the accepted filetype creation as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get(route('admin.accepted_filetype.create'))->assertForbidden();
});

it('cannot access the accepted filetype creation as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get(route('admin.accepted_filetype.create'))->assertForbidden();
});

it('can access the accepted filetype creation as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.accepted_filetype.create'))->assertOk();
});

it('cannot create an accepted filetype as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post(route('admin.accepted_filetype.store'), [
        'description' => 'Test',
        'extensions' => '.tst,.tst2',
    ])->assertForbidden();
});

it('cannot create an accepted filetype as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->post(route('admin.accepted_filetype.store'), [
        'description' => 'Test',
        'extensions' => '.tst,.tst2',
    ])->assertForbidden();
});

it('can create an accepted filetype as admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.accepted_filetype.store'), [
        'description' => 'Test',
        'extension' => '.tst,.tst2',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['success'])['success'])->toBe('Ο τύπος αρχείων δημιουργήθηκε επιτυχώς');
});

it('cannot edit an accepted filetype as user', function () {
    $user = User::factory()->user()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $this
        ->actingAs($user)
        ->get(route('admin.accepted_filetype.edit', $accepted_filetype))
        ->assertForbidden();
});

it('cannot edit an accepted filetype as author', function () {
    $author = User::factory()->author()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $this
        ->actingAs($author)
        ->get(route('admin.accepted_filetype.edit', $accepted_filetype))
        ->assertForbidden();
});

it('can edit an accepted filetype as admin', function () {
    $admin = User::factory()->admin()->create();

    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $this
        ->actingAs($admin)
        ->get(route('admin.accepted_filetype.edit', $accepted_filetype))
        ->assertOk();
});

it('cannot delete an accepted filetype as user', function () {
    $user = User::factory()->user()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $this->actingAs($user)->delete(route('admin.accepted_filetype.destroy', $accepted_filetype))->assertForbidden();
});

it('cannot delete an accepted filetype as author', function () {
    $author = User::factory()->author()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $this->actingAs($author)->delete(route('admin.accepted_filetype.destroy', $accepted_filetype))->assertForbidden();
});

it('can delete an accepted filetype as admin', function () {
    $admin = User::factory()->admin()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $response = $this->actingAs($admin)->delete(route('admin.accepted_filetype.destroy', $accepted_filetype));
    $response->assertStatus(302);
    expect($response->getSession()->only(['success'])['success'])->toBe('Επιτυχής διαγραφή τύπου αρχείων');
});

it('cannot update an accepted filetype as user', function () {
    $user = User::factory()->user()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $response = $this->actingAs($user)->put(route('admin.accepted_filetype.update', $accepted_filetype), [
        'description' => 'Test2',
        'extension' => '.tst2',
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update an accepted filetype as author', function () {
    $author = User::factory()->author()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $response = $this->actingAs($author)->put(route('admin.accepted_filetype.update', $accepted_filetype), [
        'description' => 'Test2',
        'extension' => '.tst2',
    ])->assertForbidden();
    $response->assertForbidden();
});

it('can update an accepted filetype as admin', function () {
    $admin = User::factory()->admin()->create();
    $accepted_filetype = AcceptedFiletype::factory()
        ->create(['description' => 'Test', 'extension' => '.tst']);

    $response = $this->actingAs($admin)->put(route('admin.accepted_filetype.update', $accepted_filetype), [
        'description' => 'Test2',
        'extension' => '.tst2',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['success'])['success'])->toBe('Ο τύπος αρχείων ενημερώθηκε επιτυχώς');
    $this->assertDatabaseHas('accepted_filetypes', [
        'description' => 'Test2',
        'extension' => '.tst2',
    ]);
});
