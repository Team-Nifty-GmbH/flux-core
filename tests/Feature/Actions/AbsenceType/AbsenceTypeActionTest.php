<?php

use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\AbsenceType\DeleteAbsenceType;
use FluxErp\Actions\AbsenceType\UpdateAbsenceType;

test('create absence type', function (): void {
    $type = CreateAbsenceType::make([
        'name' => 'Vacation',
        'code' => 'VAC',
        'color' => '#00FF00',
        'percentage_deduction' => 1.0,
        'employee_can_create' => 'yes',
        'affects_vacation' => true,
        'affects_overtime' => false,
        'affects_sick_leave' => false,
    ])->validate()->execute();

    expect($type)->name->toBe('Vacation');
});

test('create absence type requires name code color', function (): void {
    CreateAbsenceType::assertValidationErrors([], ['name', 'code', 'color']);
});

test('update absence type', function (): void {
    $type = CreateAbsenceType::make([
        'name' => 'Sick',
        'code' => 'SCK',
        'color' => '#FF0000',
        'percentage_deduction' => 1.0,
        'employee_can_create' => 'no',
        'affects_sick_leave' => true,
        'affects_overtime' => false,
        'affects_vacation' => false,
    ])->validate()->execute();

    $updated = UpdateAbsenceType::make([
        'id' => $type->getKey(),
        'name' => 'Sick Leave',
        'employee_can_create' => 'no',
    ])->validate()->execute();

    expect($updated->name)->toBe('Sick Leave');
});

test('delete absence type', function (): void {
    $type = CreateAbsenceType::make([
        'name' => 'Temp',
        'code' => 'TMP',
        'color' => '#999999',
        'percentage_deduction' => 0.5,
        'employee_can_create' => 'yes',
    ])->validate()->execute();

    expect(DeleteAbsenceType::make(['id' => $type->getKey()])
        ->validate()->execute())->toBeTrue();
});
