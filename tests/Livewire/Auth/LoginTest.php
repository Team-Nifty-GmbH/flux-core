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
        ->set('totpCode', '123456')
        ->call('verifyTotpCode')
        ->assertSet('showTotpChallenge', false)
        ->assertSet('totpCode', null);

    expect(Session::has('two_factor_login'))->toBeFalse();
});

test('login is locked after too many failed attempts', function (): void {
    $component = Livewire::test(Login::class)
        ->set('email', $this->user->email);

    foreach (range(1, 5) as $attempt) {
        $component->set('password', 'wrongpassword')->call('login');
    }

    $component->set('password', 'password')
        ->call('login')
        ->assertNoRedirect();

    $this->assertGuest();
});

test('totp challenge is locked after too many failed attempts', function (): void {
    $this->user->createTwoFactorAuth();
    $this->user->twoFactorAuth->forceFill(['enabled_at' => now()])->save();

    Session::put('two_factor_login', [
        'user_id' => $this->user->getKey(),
        'remember' => false,
        'target' => route('dashboard'),
    ]);

    $component = Livewire::test(Login::class);

    foreach (range(1, 5) as $attempt) {
        $component->set('totpCode', 'invalid')->call('verifyTotpCode');
    }

    $component->set('totpCode', $this->user->makeTwoFactorCode())
        ->call('verifyTotpCode')
        ->assertNoRedirect();

    $this->assertGuest();
});

test('renders successfully', function (): void {
    Livewire::test(Login::class)
        ->assertOk();
});
