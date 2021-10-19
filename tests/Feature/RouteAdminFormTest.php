<?php

use App\Models\Form;
use App\Models\Option;
use App\Models\User;
use Database\Seeders\OptionSeeder;
use Subfission\Cas\Facades\Cas;
use Tests\TestCasManager;

beforeEach(function() {
    $this->seed(OptionSeeder::class);
    $option = Option::where('name', 'first_run')->first();
    $option->value = 0;
    $option->save();

    $this->app->singleton('cas', function () {
        return new TestCasManager();
    });

    // Cas::shouldReceive('checkAuthentication')
    //     ->andReturn(NULL);
    // Cas::shouldReceive('authenticate')
    //     ->andReturn(NULL);
    // Cas::shouldReceive('getAttribute')
    //     ->andReturn(NULL);
    Cas::shouldReceive('isAuthenticated')
        ->andReturn(false);
    Cas::shouldReceive('user')
        ->andReturn(NULL);
    Cas::shouldReceive('logout')
        ->andReturn(NULL);
    Cas::shouldReceive('client')
        ->andReturn(NULL);
});


it('can access the forms panel as user', function() {
    $user = User::factory()->user()->create();

    $this->actingAs($user)->get('/admin/form')->assertOk();
});

it('can access the forms panel as author', function() {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get('/admin/form')->assertOk();
});

it('can access the forms panel as admin', function() {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/form')->assertOk();
});

it('can access a form\'s preview as user', function() {
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create(['active' => true]);
    $this->assertInstanceOf(Form::class, $testForm);

    $this->actingAs($user)->get('/admin/form/'.$testForm->id)->assertOk();
});

it('can access a form\'s preview as author', function() {
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create(['active' => true]);

    $this->actingAs($author)->get('/admin/form/'.$testForm->id)->assertOk();
});

it('can access a form\'s preview as admin', function() {
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create(['active' => true]);

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id)->assertOk();
});

it('cannot create a form as user', function(){
    $user = User::factory()->user()->create();

    $this->actingAs($user)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '']]
    ])->assertForbidden();
});

it('can create a form as author', function(){
    $author = User::factory()->author()->create();

    $response = $this->actingAs($author)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '']]
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');
});

it('can create a form as admin', function(){
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/form', [
        'title' => 'Test form',
        'notes' => 'This is a test',
        'active' => true,
        'multiple' => false,
        'field' => [['title' => 'Test field', 'type' => 0, 'values' => '']]
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα δημιουργήθηκε');
});

it('cannot edit a form as user', function(){
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create();

    $this->actingAs($user)->get('/admin/form/'.$testForm->id.'/edit')->assertForbidden();
});

it('can edit a form as author', function(){
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();

    $this->actingAs($author)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('can edit a form as admin', function(){
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();

    $this->actingAs($admin)->get('/admin/form/'.$testForm->id.'/edit')->assertOk();
});

it('cannot delete a form as user', function(){
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create();

    $this->actingAs($user)->delete('/admin/form/'.$testForm->id)->assertForbidden();
});

it('can delete a form as author', function(){
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();

    $response = $this->actingAs($author)->delete('/admin/form/'.$testForm->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα διαγράφηκε');
});

it('can delete a form as admin', function(){
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();

    $response = $this->actingAs($admin)->delete('/admin/form/'.$testForm->id);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα διαγράφηκε');
});

it('cannot update a form as user', function(){
    $user = User::factory()->user()->create();
    $testForm = Form::factory()->for($user)->create();

    $response = $this->actingAs($user)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form2',
    ])->assertForbidden();
    $response->assertForbidden();
});

it('cannot update a form as author', function(){
    $author = User::factory()->author()->create();
    $testForm = Form::factory()->for($author)->create();

    $response = $this->actingAs($author)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form2',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενημερώθηκε');
});

it('can update a form as admin', function(){
    $admin = User::factory()->admin()->create();
    $testForm = Form::factory()->for($admin)->create();

    $response = $this->actingAs($admin)->put('/admin/form/'.$testForm->id, [
        'title' => 'Test Form2',
    ]);
    $response->assertStatus(302);
    expect($response->getSession()->only(['status'])['status'])->toBe('Η φόρμα ενημερώθηκε');
});
