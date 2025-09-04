<?php

use FluxErp\Livewire\Settings\UserEdit;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('can save user', function (): void {
    $user = User::factory()
        ->for(Language::factory())
        ->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($this->user)
        ->test(UserEdit::class, ['user' => $user])
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('isSuperAdmin', false)
        ->assertSet('userForm.id', $user->id)
        ->assertSet('userForm.firstname', $user->firstname)
        ->assertSet('userForm.lastname', $user->lastname)
        ->assertSet('userForm.email', $user->email)
        ->assertSet('userForm.user_code', $user->user_code)
        ->assertSet('userForm.is_active', $user->is_active)
        ->assertSet('userForm.password', null);

    $component->set('userForm.firstname', $newFirstName = Str::uuid()->toString())
        ->set('userForm.password', 'invalid')
        ->call('save')
        ->assertHasErrors(['password']);
    expect(count(data_get($component->errors()->messages(), 'password', [])))->toEqual(4);

    $component
        ->set('userForm.password', 'Password123!')
        ->call('save')
        ->assertHasErrors(['password'])
        ->set('userForm.password_confirmation', 'Password123!')
        ->call('save')
        ->assertHasNoErrors()
        ->assertToastNotification(type: 'success');

    expect($user->fresh()->firstname)->toEqual($newFirstName);
    $this->assertNotEquals($user->password, $user->fresh()->password);
});

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(UserEdit::class, ['user' => $this->user])
        ->assertStatus(200);
});
