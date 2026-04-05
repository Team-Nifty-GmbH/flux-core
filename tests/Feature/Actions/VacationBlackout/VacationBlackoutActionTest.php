<?php

use FluxErp\Actions\VacationBlackout\CreateVacationBlackout;
use FluxErp\Actions\VacationBlackout\DeleteVacationBlackout;
use FluxErp\Actions\VacationBlackout\UpdateVacationBlackout;

test('create vacation blackout', function (): void {
    $blackout = CreateVacationBlackout::make([
        'name' => 'Christmas Freeze',
        'start_date' => '2026-12-20',
        'end_date' => '2026-12-31',
    ])->validate()->execute();

    expect($blackout)->name->toBe('Christmas Freeze');
});

test('create vacation blackout requires name start and end', function (): void {
    CreateVacationBlackout::assertValidationErrors([], ['name', 'start_date', 'end_date']);
});

test('update vacation blackout', function (): void {
    $blackout = CreateVacationBlackout::make([
        'name' => 'Original',
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-15',
    ])->validate()->execute();

    $updated = UpdateVacationBlackout::make([
        'id' => $blackout->getKey(),
        'name' => 'Summer Freeze',
    ])->validate()->execute();

    expect($updated->name)->toBe('Summer Freeze');
});

test('delete vacation blackout', function (): void {
    $blackout = CreateVacationBlackout::make([
        'name' => 'Temp',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-02',
    ])->validate()->execute();

    expect(DeleteVacationBlackout::make(['id' => $blackout->getKey()])
        ->validate()->execute())->toBeTrue();
});
