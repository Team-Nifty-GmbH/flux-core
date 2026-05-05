<?php

use FluxErp\Livewire\Auth\Login;
use FluxErp\Mail\MagicLoginLink;
use FluxErp\Settings\SecuritySettings;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAsGuest();
});

test('login link', function (): void {
    Mail::fake();

    Livewire::test(Login::class)
        ->assertOk()
        ->set('email', $this->user->email)
        ->call('login')
        ->assertNoRedirect()
        ->assertDispatched('ts-ui:toast');

    $this->assertGuest();

    Mail::assertQueued(MagicLoginLink::class);
});

test('login successful', function (): void {
    Livewire::test(Login::class)
        ->assertOk()
        ->set('email', $this->user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
});

test('login wrong password', function (): void {
    Livewire::test(Login::class)
        ->assertOk()
        ->set('email', 'noexistingmail@example.com')
        ->set('password', 'wrongpassword')
        ->call('login')
        ->assertNoRedirect()
        ->assertDispatched('ts-ui:toast');

    $this->assertGuest();
});

test('login link is rejected when system setting is disabled', function (): void {
    SecuritySettings::fake([
        'magic_login_links_enabled' => false,
    ]);
    Mail::fake();

    Livewire::test(Login::class)
        ->set('email', $this->user->email)
        ->call('login')
        ->assertNoRedirect()
        ->assertDispatched('ts-ui:toast');

    Mail::assertNothingQueued();
    $this->assertGuest();
});

test('verifyTotpCode resets state when session payload is malformed', function (): void {
    Session::put('two_factor_login', ['user_id' => $this->user->getKey()]);

    Livewire::test(Login::class)
        ->set('showTotpChallenge', true)
        ->set('totpCode', '123456')
        ->call('verifyTotpCode')
        ->assertSet('showTotpChallenge', false)
        ->assertSet('totpCode', null);

    expect(Session::has('two_factor_login'))->toBeFalse();
});

test('renders successfully', function (): void {
    Livewire::test(Login::class)
        ->assertOk();
});
