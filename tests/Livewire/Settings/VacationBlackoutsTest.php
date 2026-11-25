<?php

use FluxErp\Livewire\Settings\VacationBlackouts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VacationBlackouts::class)
        ->assertOk();
});

test('can create vacation blackout with employee departments', function (): void {
    $department = FluxErp\Models\EmployeeDepartment::query()->create(['name' => 'Support']);

    Livewire::test(VacationBlackouts::class)
        ->call('edit')
        ->set('vacationBlackoutForm.name', 'Christmas Freeze')
        ->set('vacationBlackoutForm.start_date', '2026-12-20')
        ->set('vacationBlackoutForm.end_date', '2026-12-31')
        ->set('vacationBlackoutForm.employee_departments', [$department->getKey()])
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect(
        FluxErp\Models\VacationBlackout::query()
            ->where('name', 'Christmas Freeze')
            ->first()
            ->employeeDepartments()
            ->pluck('employee_departments.id')
            ->all()
    )->toBe([$department->getKey()]);
});
