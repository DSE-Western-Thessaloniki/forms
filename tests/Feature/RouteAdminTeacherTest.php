<?php

use App\Models\Option;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\OptionSeeder;

beforeEach(function () {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();
});

it('cannot access the teacher panel as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/teacher')->assertForbidden();
});

it('cannot access the teacher panel as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/teacher')->assertForbidden();
});

it('can access the teacher panel as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/teacher')->assertOk();
});

it('cannot access a teacher\'s creation form as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/teacher/create')->assertForbidden();
});

it('cannot access a teacher\'s creation form as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/teacher/create')->assertForbidden();
});

it('can access a teacher\'s creation form as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/teacher/create')->assertOk();
});

it('cannot create a teacher as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post('/admin/teacher', [
        'surname' => 'Doe',
        'name' => 'Joe',
        'am' => '100',
        'afm' => '101',
    ])->assertForbidden();
});

it('cannot create a teacher as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->post('/admin/teacher', [
        'surname' => 'Doe',
        'name' => 'Joe',
        'am' => '100',
        'afm' => '101',
    ])->assertForbidden();
});

it('can create a teacher as admin', function () {
    $admin = User::factory()->admin()->create();
    $teacher_data = [
        'surname' => 'Doe',
        'name' => 'Joe',
        'am' => '100',
        'afm' => '101',
    ];

    $response = $this->actingAs($admin)->post('/admin/teacher', $teacher_data);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Ο εκπαιδευτικός αποθηκεύτηκε!');
    $this->assertDatabaseHas('teachers', $teacher_data);
});

it('cannot edit a teacher as user', function () {
    $user = User::factory()->user()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs($user)->get('/admin/teacher/'.$teacher->id.'/edit')->assertForbidden();
});

it('cannot edit a teacher as author', function () {
    $author = User::factory()->author()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs($author)->get('/admin/teacher/'.$teacher->id.'/edit')->assertForbidden();
});

it('can edit a teacher as admin', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs($admin)->get('/admin/teacher/'.$teacher->id.'/edit')->assertOk();
});

it('cannot delete a teacher as user', function () {
    $user = User::factory()->user()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs($user)->delete('/admin/teacher/'.$teacher->id)->assertForbidden();
});

it('cannot delete a teacher as author', function () {
    $author = User::factory()->author()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs($author)->delete('/admin/teacher/'.$teacher->id)->assertForbidden();
});

it('can delete a teacher as admin', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();

    $response = $this->actingAs($admin)->delete('/admin/teacher/'.$teacher->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Ο εκπαιδευτικός διαγράφηκε!');
});

it('cannot update a teacher as user', function () {
    $user = User::factory()->user()->create();
    $teacher = Teacher::factory()->create();

    $new_teacher_data = [
        'surname' => 'Doe',
        'name' => 'Joe',
        'am' => '100',
        'afm' => '101',
    ];

    $response = $this->actingAs($user)->put('/admin/teacher/'.$teacher->id, $new_teacher_data)->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a teacher as author', function () {
    $author = User::factory()->author()->create();
    $teacher = Teacher::factory()->create();

    $new_teacher_data = [
        'surname' => 'Doe',
        'name' => 'Joe',
        'am' => '100',
        'afm' => '101',
    ];

    $response = $this->actingAs($author)->put('/admin/teacher/'.$teacher->id, $new_teacher_data)->assertForbidden();
    $response->assertForbidden();
});

it('can update a teacher as admin', function () {
    $admin = User::factory()->admin()->create();
    $teacher = Teacher::factory()->create();

    $new_teacher_data = [
        'surname' => 'Doe',
        'name' => 'Joe',
        'am' => '100',
        'afm' => '101',
    ];

    $response = $this->actingAs($admin)->put('/admin/teacher/'.$teacher->id, $new_teacher_data);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Ο εκπαιδευτικός ενημερώθηκε!');
});
