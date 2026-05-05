<?php

use FluxErp\Enums\TwoFactorMethodEnum;
use FluxErp\Livewire\Auth\ForceTwoFactorSetup;
use Livewire\Livewire;

test('redirects to dashboard when force_two_factor is not set', function (): void {
    Livewire::test(ForceTwoFactorSetup::class)
        ->assertRedirect(route('dashboard'));
});

test('redirects to dashboard when user already has a configured method', function (): void {
    $this->user->update(['force_two_factor' => true]);
    $this->user->createTwoFactorAuth();
    $this->user->confirmTwoFactorAuth($this->user->makeTwoFactorCode());

    Livewire::test(ForceTwoFactorSetup::class)
        ->assertRedirect(route('dashboard'));
});

test('renders the choice screen for forced users without a method', function (): void {
    $this->user->update(['force_two_factor' => true]);

    Livewire::test(ForceTwoFactorSetup::class)
        ->assertOk()
        ->assertNoRedirect()
        ->assertSet('method', null);
});

test('selectTotp sets method and exposes the QR + secret', function (): void {
    $this->user->update(['force_two_factor' => true]);

    Livewire::test(ForceTwoFactorSetup::class)
        ->call('selectTotp')
        ->assertSet('method', TwoFactorMethodEnum::Totp)
        ->assertSet('qrCodeSvg', $this->user->fresh()->twoFactorAuth?->toQr())
        ->assertSet('secretKey', $this->user->fresh()->twoFactorAuth?->shared_secret);
});

test('confirmTotp redirects to dashboard on valid code', function (): void {
    $this->user->update(['force_two_factor' => true]);
    $this->user->createTwoFactorAuth();
    $code = $this->user->makeTwoFactorCode();

    Livewire::test(ForceTwoFactorSetup::class)
        ->set('method', TwoFactorMethodEnum::Totp)
        ->set('confirmCode', $code)
        ->call('confirmTotp')
        ->assertRedirect(route('dashboard'));

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('confirmTotp resets and toasts on invalid code', function (): void {
    $this->user->update(['force_two_factor' => true]);
    $this->user->createTwoFactorAuth();

    Livewire::test(ForceTwoFactorSetup::class)
        ->set('method', TwoFactorMethodEnum::Totp)
        ->set('confirmCode', '000000')
        ->call('confirmTotp')
        ->assertNoRedirect()
        ->assertSet('confirmCode', null)
        ->assertDispatched('ts-ui:toast');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('passkeyStored toasts when user has no passkey yet', function (): void {
    $this->user->update(['force_two_factor' => true]);

    Livewire::test(ForceTwoFactorSetup::class)
        ->set('method', TwoFactorMethodEnum::Passkey)
        ->call('passkeyStored')
        ->assertNoRedirect()
        ->assertDispatched('ts-ui:toast');
});

test('back rolls TOTP setup back to the choice screen', function (): void {
    $this->user->update(['force_two_factor' => true]);

    Livewire::test(ForceTwoFactorSetup::class)
        ->call('selectTotp')
        ->assertSet('method', TwoFactorMethodEnum::Totp)
        ->call('back')
        ->assertSet('method', null)
        ->assertSet('qrCodeSvg', null)
        ->assertSet('secretKey', null);

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});
