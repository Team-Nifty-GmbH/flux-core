<?php

use FluxErp\Livewire\Employee\Dashboard;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Dashboard::class)
        ->assertOk();
});

test('does not accept another employee from the client', function (): void {
    Livewire::test(Dashboard::class, ['employeeId' => 1])
        ->set('employeeId', 2);
})->throws(CannotUpdateLockedPropertyException::class);
