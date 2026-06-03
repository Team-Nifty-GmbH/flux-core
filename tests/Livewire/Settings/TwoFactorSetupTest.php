<?php

use FluxErp\Livewire\Settings\TwoFactorSetup;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TwoFactorSetup::class)
        ->assertOk();
});

test('disableTwoFactor refuses to disable when force_two_factor is set on the user', function (): void {
    $this->user->update(['force_two_factor' => true]);
    $this->user->createTwoFactorAuth();
    $this->user->confirmTwoFactorAuth($this->user->makeTwoFactorCode());

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();

    Livewire::test(TwoFactorSetup::class)
        ->call('disableTwoFactor')
        ->assertDispatched('ts-ui:toast');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('disableTwoFactor works for non-forced users', function (): void {
    $this->user->createTwoFactorAuth();
    $this->user->confirmTwoFactorAuth($this->user->makeTwoFactorCode());

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();

    Livewire::test(TwoFactorSetup::class)
        ->call('disableTwoFactor')
        ->assertDispatched('ts-ui:toast');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('cancelSetup refuses when forced user already has 2FA enabled', function (): void {
    $this->user->update(['force_two_factor' => true]);
    $this->user->createTwoFactorAuth();
    $this->user->confirmTwoFactorAuth($this->user->makeTwoFactorCode());

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();

    Livewire::test(TwoFactorSetup::class)
        ->call('cancelSetup')
        ->assertDispatched('ts-ui:toast');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});
