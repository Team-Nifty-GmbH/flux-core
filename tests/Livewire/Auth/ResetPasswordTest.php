<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Auth\ResetPassword;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ResetPassword::class)
        ->assertStatus(200);
});
