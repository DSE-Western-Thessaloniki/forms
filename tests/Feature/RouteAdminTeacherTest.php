<?php

use App\Models\Option;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Illuminate\Http\UploadedFile;

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

it('can access the teacher panel as admin (use filter)', function () {
    $admin = User::factory()->admin()->create();
    $teachers = Teacher::factory()
        ->createMany(2);
    $filter = $teachers[0]->afm;

    $response = $this->actingAs($admin)->call('GET', '/admin/teacher', [
        'teacher_filter' => $filter,
    ])->assertOk();

    $response->assertSee($teachers[0]->surname);
    $response->assertDontSee($teachers[1]->surname);
})->only();

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

it('cannot access a teacher\'s import form as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/teacher/import')->assertForbidden();
});

it('cannot access a teacher\'s import form as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/teacher/import')->assertForbidden();
});

it('can access a teacher\'s import form as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/teacher/import')->assertOk();
});

it('cannot import teachers as user', function () {
    $user = User::factory()->user()->create();
    $file = UploadedFile::fake()->createWithContent('test.csv', "Doe;Joe;100;101\nDoe;Jane;101;102\n");

    $this->actingAs($user)->post('/admin/teacher/import', ['csvfile' => $file])->assertForbidden();
});

it('cannot import teachers as author', function () {
    $author = User::factory()->author()->create();
    $file = UploadedFile::fake()->createWithContent('test.csv', "Doe;Joe;100;101\nDoe;Jane;101;102\n");

    $this->actingAs($author)->post('/admin/teacher/import', ['csvfile' => $file])->assertForbidden();
});

it('can import teachers as admin (semicolon as delimiter)', function () {
    $admin = User::factory()->admin()->create();
    $file = UploadedFile::fake()->createWithContent('test.csv', "Doe;Joe;100;101\nDoe;Jane;101;102\n");

    $response = $this->actingAs($admin)->post('/admin/teacher/import', ['csvfile' => $file])->assertRedirect('/admin/teacher');
    expect($response->getSession()->only(['success'])['success'])->toBe('Έγινε εισαγωγή 2 εκπαιδευτικών');
});

it('can import teachers as admin (comma as delimiter)', function () {
    $admin = User::factory()->admin()->create();
    $file = UploadedFile::fake()->createWithContent('test.csv', "Doe,Joe,100,101\nDoe,Jane,101,102\n");

    $response = $this->actingAs($admin)->post('/admin/teacher/import', ['csvfile' => $file])->assertRedirect('/admin/teacher');
    expect($response->getSession()->only(['success'])['success'])->toBe('Έγινε εισαγωγή 2 εκπαιδευτικών');
});

it('cannot import teachers as admin (wrong format of file)', function () {
    $admin = User::factory()->admin()->create();
    $file = UploadedFile::fake()->createWithContent('test.csv', "Doe,Joe,100,101\nDoe,Jane,101\n");

    $response = $this->actingAs($admin)->post('/admin/teacher/import', ['csvfile' => $file])->assertRedirect('/admin/teacher');
    expect($response->getSession()->only(['error'])['error'])->toBe('Λανθασμένη μορφή αρχείου');
});

it('cannot import teachers as admin (wrong am/afm combination)', function () {
    $admin = User::factory()->admin()->create();
    Teacher::factory()->create([
        'name' => 'Joe',
        'surname' => 'Doe',
        'am' => '100',
        'afm' => '120',
    ]);
    $file = UploadedFile::fake()->createWithContent('test.csv', "Doe,Joe,100,101\nDoe,Jane,101,102\n");

    $response = $this->actingAs($admin)->post('/admin/teacher/import', ['csvfile' => $file])->assertRedirect('/admin/teacher');
    expect($response->getSession()->only(['error'])['error'])->toBe('Ασυμφωνία ΑΜ/ΑΦΜ με τη βάση για τον εκπαιδευτικό του πίνακα Doe Joe ΑΜ: 100 ΑΦΜ: 101');
});
