<?php

use FluxErp\Livewire\Auth\Login;
use Livewire\Livewire;

test('login as authenticated user', function (): void {
    $this->actingAs($this->user, 'web')->get('/login')
        ->assertFound()
        ->assertRedirect();
});

test('login no path', function (): void {
    $this->actingAsGuest();
    $this->get('/')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('login page', function (): void {
    $this->actingAsGuest();
    $this->get('/login')
        ->assertOk();
});

test('post login with password logs in a user without two factor', function (): void {
    $this->actingAsGuest();

    $this->post('/login', ['email' => $this->user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
});

test('post login requires the totp code when two factor is enabled', function (): void {
    $this->actingAsGuest();
    $this->user->createTwoFactorAuth();
    $this->user->confirmTwoFactorAuth($this->user->makeTwoFactorCode());

    $this->post('/login', ['email' => $this->user->email, 'password' => 'password'])
        ->assertRedirect(route('login'));

    $this->assertGuest();
    $this->get(route('dashboard'))->assertRedirect(route('login'));

    $this->travel(30)->seconds();

    Livewire::test(Login::class)
        ->assertSet('showTotpChallenge', true)
        ->set('totpCode', $this->user->makeTwoFactorCode())
        ->call('verifyTotpCode')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
});

test('totp code does not log in a user deactivated after the password step', function (): void {
    $this->actingAsGuest();
    $this->user->createTwoFactorAuth();
    $this->user->confirmTwoFactorAuth($this->user->makeTwoFactorCode());

    $this->post('/login', ['email' => $this->user->email, 'password' => 'password'])
        ->assertRedirect(route('login'));

    $this->user->update(['is_active' => false]);
    $this->travel(30)->seconds();

    Livewire::test(Login::class)
        ->set('totpCode', $this->user->makeTwoFactorCode())
        ->call('verifyTotpCode')
        ->assertNoRedirect();

    $this->assertGuest();
});
