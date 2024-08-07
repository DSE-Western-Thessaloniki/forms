<?php

use App\Models\Option;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();
});

it('cannot access the schools panel as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/school')->assertForbidden();
});

it('cannot access the schools panel as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/school')->assertForbidden();
});

it('can access the schools panel as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/school')->assertOk();
});

it('can access the schools panel as admin with filter', function () {
    $admin = User::factory()->admin()->create();
    $testSchools = School::factory(2)
        ->category()
        ->for($admin)
        ->create();
    $category = SchoolCategory::factory()->create(['name' => 'Test Category 1']);
    foreach ($testSchools as $testSchool) {
        $testSchool->categories()->attach($category);
    }

    $response = $this->actingAs($admin)->get('/admin/school')->assertOk();
    $response->assertSee($testSchools[0]->name);
    $response->assertSee($testSchools[1]->name);

    $response = $this->actingAs($admin)->call('GET', '/admin/school', [
        'filter' => $testSchools[0]->name,
    ])->assertOk();
    $response->assertSee($testSchools[0]->name);
    $response->assertDontSee($testSchools[1]->name);
});

it('cannot access the school creation as user', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/school/create')->assertForbidden();
});

it('cannot access the school creation as author', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/school/create')->assertForbidden();
});

it('can access the school creation as admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/school/create')->assertOk();
});

it('cannot access a school\'s info as user', function () {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $this->actingAs($user)->get('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('cannot access a school\'s info as author', function () {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $this->actingAs($author)->get('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('can access a school\'s info as admin', function () {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $this->actingAs($admin)->get('/admin/school/'.$testSchool->id)->assertOk();
});

it('cannot create a school as user', function () {
    $user = User::factory()->user()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $this->actingAs($user)->post('/admin/school', [
        'name' => 'Test School',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testSchool',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
});

it('cannot create a school as author', function () {
    $author = User::factory()->author()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $this->actingAs($author)->post('/admin/school', [
        'name' => 'Test School',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testSchool',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
});

it('can create a school as admin', function () {
    $admin = User::factory()->admin()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($admin)->post('/admin/school', [
        'name' => 'Test School',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testSchool',
        'code' => '9999999',
        'category' => strval($category->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα αποθηκεύτηκε!');
});

it('cannot create a school as user with invalid categories', function () {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post('/admin/school', [
        'name' => 'Test School',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testSchool',
        'code' => '9999999',
        'category' => '0,1',
    ])->assertForbidden();
});

it('cannot create a school as author with invalid categories', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->post('/admin/school', [
        'name' => 'Test School',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testSchool',
        'code' => '9999999',
        'category' => '0,1',
    ])->assertForbidden();
});

it('cannot create a school as admin with invalid categories', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/school', [
        'name' => 'Test School',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testSchool',
        'code' => '9999999',
        'category' => '0,1',
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['status'])['status'])->toBe('Άκυρες κατηγορίες');
});

it('cannot edit a school as user', function () {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 1']));
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 2']));

    $this->actingAs($user)->get('/admin/school/'.$testSchool->id.'/edit')->assertForbidden();
});

it('cannot edit a school as author', function () {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 1']));
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 2']));

    $this->actingAs($author)->get('/admin/school/'.$testSchool->id.'/edit')->assertForbidden();
});

it('can edit a school as admin', function () {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 1']));
    $testSchool->categories()->attach(SchoolCategory::factory()->create(['name' => 'Test Category 2']));

    $this->actingAs($admin)->get('/admin/school/'.$testSchool->id.'/edit')->assertOk();
});

it('cannot delete a school as user', function () {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $this->actingAs($user)->delete('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('cannot delete a school as author', function () {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $this->actingAs($author)->delete('/admin/school/'.$testSchool->id)->assertForbidden();
});

it('can delete a school as admin', function () {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $response = $this->actingAs($admin)->delete('/admin/school/'.$testSchool->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα διαγράφηκε!');
});

it('cannot update a school as user', function () {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($user)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school as author', function () {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($author)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ])->assertForbidden();
    $response->assertForbidden();
});

it('can update a school as admin', function () {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    $response = $this->actingAs($admin)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => strval($category->id),
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η σχολική μονάδα ενημερώθηκε!');
});

it('cannot update a school as user with invalid categories', function () {
    $user = User::factory()->user()->create();
    $testSchool = School::factory()->for($user)->create(['name' => 'Test School']);

    $response = $this->actingAs($user)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => '0,1',
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school as author with invalid categories', function () {
    $author = User::factory()->author()->create();
    $testSchool = School::factory()->for($author)->create(['name' => 'Test School']);

    $response = $this->actingAs($author)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => '0,1',
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a school as admin with invalid categories', function () {
    $admin = User::factory()->admin()->create();
    $testSchool = School::factory()->for($admin)->create(['name' => 'Test School']);

    $response = $this->actingAs($admin)->put('/admin/school/'.$testSchool->id, [
        'name' => 'Test School2',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
        'username' => 'testUser',
        'code' => '9999999',
        'category' => '0,1',
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['status'])['status'])->toBe('Άκυρες κατηγορίες');
});

it('cannot import a school as user', function () {
    $user = User::factory()->user()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
        'Test School2,testUser,9999999,test@example.com,123-456-7890,testCategory');

    $response = $this->actingAs($user)->post('/admin/school/import', [
        'csvfile' => $file,
    ]);
    $response->assertForbidden();
});

it('cannot import a school as author', function () {
    $author = User::factory()->author()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
        'Test School2,testUser,9999999,test@example.com,123-456-7890,testCategory');

    $response = $this->actingAs($author)->post('/admin/school/import', [
        'csvfile' => $file,
    ]);
    $response->assertForbidden();
});

it('can import a school as admin', function () {
    $admin = User::factory()->admin()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
        'Test School2,testUser,9999999,test@example.com,123-456-7890,testCategory');

    $response = $this->actingAs($admin)->post('/admin/school/import', [
        'csvfile' => $file,
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['success'])['success'])->toBe('Έγινε εισαγωγή 1 σχολικών μονάδων');
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School2',
        'username' => 'testUser',
        'code' => '9999999',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
    ]);
    $this->assertDatabaseCount('school_category_school', 1);
    $this->assertDatabaseHas('school_category_school', [
        'school_category_id' => $category->id,
    ]);
});

it('can import a school as admin (with ; as delimiter)', function () {
    $admin = User::factory()->admin()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
        'Test School2;testUser;9999999;test@example.com;123-456-7890;testCategory');

    $response = $this->actingAs($admin)->post('/admin/school/import', [
        'csvfile' => $file,
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['success'])['success'])->toBe('Έγινε εισαγωγή 1 σχολικών μονάδων');
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School2',
        'username' => 'testUser',
        'code' => '9999999',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
    ]);
    $this->assertDatabaseCount('school_category_school', 1);
    $this->assertDatabaseHas('school_category_school', [
        'school_category_id' => $category->id,
    ]);
});

it('can import multiple schools as admin', function () {
    $admin = User::factory()->admin()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
        'Test School1,testUser,9999999,test@example.com,123-456-7890,testCategory
Test School2,testUser2,9999991,test2@example.com,123-456-7891,testCategory
Test School3,testUser3,9999992,test3@example.com,123-456-7892,testCategory2');

    $response = $this->actingAs($admin)->post('/admin/school/import', [
        'csvfile' => $file,
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['success'])['success'])->toBe('Έγινε εισαγωγή 3 σχολικών μονάδων');
    $this->assertDatabaseCount('schools', 3);
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School1',
        'username' => 'testUser',
        'code' => '9999999',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
    ]);
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School2',
        'username' => 'testUser2',
        'code' => '9999991',
        'email' => 'test2@example.com',
        'telephone' => '123-456-7891',
    ]);
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School3',
        'username' => 'testUser3',
        'code' => '9999992',
        'email' => 'test3@example.com',
        'telephone' => '123-456-7892',
    ]);

    expect(SchoolCategory::where('name', 'testCategory')->first()->schools()->count())->toBe(2);
    expect(SchoolCategory::where('name', 'testCategory2')->first()->schools()->count())->toBe(1);
});

it('cannot import multiple schools as admin (bad file)', function () {
    $admin = User::factory()->admin()->create();
    $category = SchoolCategory::factory()->create(['name' => 'testCategory']);

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
        'Test School1;testUser;9999999;test@example.com;123-456-7890;testCategory
Test School2;testUser2;9999991;test2@example.com;123-456-7891
Test School3;testUser3;9999992;test3@example.com;123-456-7892;testCategory2');

    $response = $this->actingAs($admin)->post('/admin/school/import', [
        'csvfile' => $file,
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['error'])['error'])->toBe('Λανθασμένη μορφή αρχείου');
    $this->assertDatabaseCount('schools', 0);
    $this->assertDatabaseCount('school_category_school', 0);
});

it('can import multiple schools as admin (update already existing schools)', function () {
    $admin = User::factory()->admin()->create();
    $schools = School::factory()
        ->count(3)
        ->state(new Sequence(
            ['code' => '9999999'],
            ['code' => '9999991'],
            ['code' => '9999992'],
        ))
        ->category()->for($admin)->create();
    $category1 = SchoolCategory::factory()->create(['name' => 'testCategory']);
    $category2 = SchoolCategory::factory()->create(['name' => 'testCategory2']);
    $category1->schools()->attach($schools[0]);
    $category1->schools()->attach($schools[1]);
    $category2->schools()->attach($schools[2]);

    Storage::fake('uploads');

    $file = UploadedFile::fake()->createWithContent('import_test.csv',
        'Test School1,testUser,9999999,test@example.com,123-456-7890,none
Test School2,testUser2,9999991,test2@example.com,123-456-7891,none
Test School3,testUser3,9999992,test3@example.com,123-456-7892,none');

    $response = $this->actingAs($admin)->post('/admin/school/import', [
        'csvfile' => $file,
    ]);
    $response->assertRedirect(route('admin.school.index'));
    expect($response->getSession()->only(['success'])['success'])->toBe('Έγινε εισαγωγή 3 σχολικών μονάδων');
    $this->assertDatabaseCount('schools', 3);
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School1',
        'username' => 'testUser',
        'code' => '9999999',
        'email' => 'test@example.com',
        'telephone' => '123-456-7890',
    ]);
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School2',
        'username' => 'testUser2',
        'code' => '9999991',
        'email' => 'test2@example.com',
        'telephone' => '123-456-7891',
    ]);
    $this->assertDatabaseHas('schools', [
        'name' => 'Test School3',
        'username' => 'testUser3',
        'code' => '9999992',
        'email' => 'test3@example.com',
        'telephone' => '123-456-7892',
    ]);

    expect(SchoolCategory::where('name', 'testCategory')->first()->schools()->count())->toBe(2);
    expect(SchoolCategory::where('name', 'testCategory2')->first()->schools()->count())->toBe(1);
});
