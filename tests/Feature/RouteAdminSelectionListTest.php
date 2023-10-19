<?php

use App\Models\Option;
use App\Models\SelectionList;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function() {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();
});


it('cannot access the lists panel as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/selection_list')->assertForbidden();
});

it('can access the lists panel as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/selection_list')->assertOk();
});

it('can access the lists panel as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/selection_list')->assertOk();
});

it('cannot access the list creation as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/selection_list/create')->assertForbidden();
});

it('cannot access the list creation as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/selection_list/create')->assertOk();
});

it('can access the list creation as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/selection_list/create')->assertOk();
});

it('cannot access a list\'s info as user', function() {
    $user = User::factory()->user()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => "[]",
        'created_by' => $user->id
    ]);

    $this->actingAs($user)->get('/admin/selection_list/'.$testList->id)->assertStatus(405);
});

it('cannot access a list\'s info as author', function() {
    $author = User::factory()->author()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => "[]",
        'created_by' => $author->id
    ]);

    $this->actingAs($author)->get('/admin/selection_list/'.$testList->id)->assertStatus(405);
});

it('cannot access a list\'s info as admin', function() {
    $admin = User::factory()->admin()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => "[]",
        'created_by' => $admin->id
    ]);

    $this->actingAs($admin)->get('/admin/selection_list/'.$testList->id)->assertStatus(405);
});

it('cannot create a list as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post('/admin/selection_list', [
        'name' => "Test List",
        'id' => ["0"],
        'value' => ["Test"],
        'active' => true,
        'created_by' => $user->id,
    ])->assertForbidden();
});

it('can create a list as author', function() {
    $author = User::factory()->author()->create();

    $response = $this->actingAs($author)->post('/admin/selection_list', [
        'name' => "Test List",
        'id' => ["0"],
        'value' => ["Test"],
        'active' => true,
        'created_by' => $author->id,
    ])->assertRedirect("/admin/selection_list");

    expect($response->getSession()->only(['status'])['status'])->toBe('Η λίστα αποθηκεύτηκε!');

    assertDatabaseCount('selection_lists', 1);
    assertDatabaseHas('selection_lists', [
        'name' => "Test List",
        'data' => '[{"id":0,"value":"Test"}]',
        'active' => true,
        'created_by' => $author->id,
    ]);
});

it('can create a list as admin', function() {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/selection_list', [
        'name' => "Test List",
        'id' => ["0"],
        'value' => ["Test"],
        'active' => true,
        'created_by' => $admin->id,
    ])->assertRedirect("/admin/selection_list");

    expect($response->getSession()->only(['status'])['status'])->toBe('Η λίστα αποθηκεύτηκε!');

    assertDatabaseCount('selection_lists', 1);
    assertDatabaseHas('selection_lists', [
        'name' => "Test List",
        'data' => '[{"id":0,"value":"Test"}]',
        'active' => true,
        'created_by' => $admin->id,
    ]);
});

it('cannot edit a list as user', function() {
    $user = User::factory()->user()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $user->id
    ]);

    $this->actingAs($user)->get('/admin/selection_list/'.$testList->id.'/edit')->assertForbidden();
});

it('can edit a list as author', function() {
    $author = User::factory()->author()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $author->id
    ]);

    $this->actingAs($author)->get('/admin/selection_list/'.$testList->id.'/edit')->assertOk();
});

it('can edit a list as admin', function() {
    $admin = User::factory()->admin()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $admin->id
    ]);

    $this->actingAs($admin)->get('/admin/selection_list/'.$testList->id.'/edit')->assertOk();
});

it('cannot delete a list as user', function() {
    $user = User::factory()->user()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $user->id
    ]);

    $this->actingAs($user)->delete('/admin/selection_list/'.$testList->id)->assertForbidden();

    assertDatabaseCount('selection_lists', 1);
});

it('cannot delete a list as author with no ownership', function() {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->author()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $admin->id
    ]);

    $this->actingAs($author)->delete('/admin/selection_list/'.$testList->id)->assertForbidden();

    assertDatabaseCount('selection_lists', 1);
});

it('can delete a list as author with ownership', function() {
    $author = User::factory()->author()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $author->id
    ]);

    $response = $this
        ->actingAs($author)->delete('/admin/selection_list/'.$testList->id)
        ->assertRedirect("/admin/selection_list");
    expect($response->getSession()->only(['status'])['status'])->toBe('Η λίστα διαγράφηκε!');

    assertDatabaseCount('selection_lists', 0);
});

it('can delete a list as admin', function() {
    $admin = User::factory()->admin()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $admin->id
    ]);

    $response = $this
        ->actingAs($admin)->delete('/admin/selection_list/'.$testList->id)
        ->assertRedirect("/admin/selection_list");
    expect($response->getSession()->only(['status'])['status'])->toBe('Η λίστα διαγράφηκε!');
});

it('cannot update a list as user', function() {
    $user = User::factory()->user()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $user->id
    ]);

    $response = $this->actingAs($user)->put('/admin/selection_list/'.$testList->id, [
        'name' => 'Test List2',
        'active' => false,
        'data' => '["id":0,"value":"Test"]',
    ])->assertForbidden();
});

it('cannot update a list as author with no ownership', function() {
    $author = User::factory()->author()->create();
    $author2 = User::factory()->author()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $author2->id
    ]);

    $response = $this->actingAs($author)->put('/admin/selection_list/'.$testList->id, [
        'name' => 'Test List2',
        'active' => false,
        'data' => '["id":0,"value":"Test"]',
    ])->assertForbidden();
});

it('can update a list as author with ownership', function() {
    $author = User::factory()->author()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $author->id
    ]);

    $response = $this->actingAs($author)->put('/admin/selection_list/'.$testList->id, [
        'name' => 'Test List2',
        'active' => false,
        'id' => ["0"],
        'value' => ["Test"]
    ])->assertRedirect("/admin/selection_list");
    assertDatabaseHas('selection_lists', [
        'name' => 'Test List2',
        'active' => false,
        'data' => '[{"id":0,"value":"Test"}]',
        'created_by' => $author->id
    ]);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η λίστα ενημερώθηκε επιτυχώς!');
});

it('can update a list as admin without ownership', function() {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->author()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $author->id
    ]);

    $response = $this->actingAs($admin)->put('/admin/selection_list/'.$testList->id, [
        'name' => 'Test List2',
        'active' => false,
        'id' => ["0"],
        'value' => ["Test"]
    ])->assertRedirect("/admin/selection_list");
    assertDatabaseHas('selection_lists', [
        'name' => 'Test List2',
        'active' => false,
        'data' => '[{"id":0,"value":"Test"}]',
        'created_by' => $author->id
    ]);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η λίστα ενημερώθηκε επιτυχώς!');
});

it('can update a list as admin with ownership', function() {
    $admin = User::factory()->admin()->create();
    $testList = SelectionList::factory()->create([
        'name' => 'Test List',
        'active' => true,
        'data' => '[]',
        'created_by' => $admin->id
    ]);

    $response = $this->actingAs($admin)->put('/admin/selection_list/'.$testList->id, [
        'name' => 'Test List2',
        'active' => false,
        'id' => ["0"],
        'value' => ["Test"]
    ])->assertRedirect("/admin/selection_list");
    assertDatabaseHas('selection_lists', [
        'name' => 'Test List2',
        'active' => false,
        'data' => '[{"id":0,"value":"Test"}]',
        'created_by' => $admin->id
    ]);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η λίστα ενημερώθηκε επιτυχώς!');
});

it('cannot import a list as user', function() {
    $user = User::factory()->user()->create();

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
    "Test List\ntestUser\n9999999");

    $response = $this->actingAs($user)->post('/admin/selection_list/import', [
        'csvfile' => $file,
    ]);
    $response->assertForbidden();
});

it('can import a list as author', function() {
    $author = User::factory()->author()->create();

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
    "Test List\ntestUser\n9999999");

    $response = $this->actingAs($author)->post('/admin/selection_list/import', [
        'csvfile' => $file,
    ]);
    $response->assertRedirect("/admin/selection_list");
    expect($response->getSession()->only(['status'])['status'])->toBe('Έγινε εισαγωγή νέας λίστας');
    assertDatabaseCount('selection_lists', 1);
    assertDatabaseHas('selection_lists', [
        'name' => 'Test List',
        'data' => '[{"id":0,"value":"testUser"},{"id":1,"value":"9999999"}]'
    ]);
});

it('can import a list as admin (with ; as delimiter)', function() {
    $admin = User::factory()->admin()->create();

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
    "\"Test List\";\"indiff\"\n\"testUser\";\"indiff\"\n\"9999999\";\"indiff\"");

    $response = $this->actingAs($admin)->post('/admin/selection_list/import', [
        'csvfile' => $file,
    ]);
    $response->assertRedirect(route('admin.list.index'));
    expect($response->getSession()->only(['status'])['status'])->toBe('Έγινε εισαγωγή νέας λίστας');
    assertDatabaseCount('selection_lists', 1);
    assertDatabaseHas('selection_lists', [
        'name' => 'Test List',
        'data' => '[{"id":0,"value":"testUser"},{"id":1,"value":"9999999"}]'
    ]);
});
