<?php

use FluxErp\Livewire\Auth\ResetPassword;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ResetPassword::class)
        ->assertOk();
});
