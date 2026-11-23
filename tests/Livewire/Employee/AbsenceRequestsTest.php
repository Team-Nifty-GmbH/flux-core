<?php

use FluxErp\Livewire\Employee\AbsenceRequests;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsenceRequests::class)
        ->assertOk();
});

test('does not accept another employee from the client', function (): void {
    Livewire::test(AbsenceRequests::class, ['employeeId' => 1])
        ->set('employeeId', 2);
})->throws(CannotUpdateLockedPropertyException::class);
