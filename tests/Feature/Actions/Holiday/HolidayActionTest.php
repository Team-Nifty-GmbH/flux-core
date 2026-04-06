<?php

use FluxErp\Actions\Holiday\CreateHoliday;
use FluxErp\Actions\Holiday\DeleteHoliday;
use FluxErp\Actions\Holiday\UpdateHoliday;

test('create holiday with fixed date', function (): void {
    $holiday = CreateHoliday::make([
        'name' => 'New Year',
        'date' => '2026-01-01',
        'day_part' => 'full_day',
        'is_recurring' => false,
    ])->validate()->execute();

    expect($holiday)->name->toBe('New Year');
});

test('create recurring holiday', function (): void {
    $holiday = CreateHoliday::make([
        'name' => 'Christmas',
        'day' => 25,
        'month' => 12,
        'day_part' => 'full_day',
        'is_recurring' => true,
    ])->validate()->execute();

    expect($holiday)
        ->name->toBe('Christmas')
        ->is_recurring->toBeTrue();
});

test('create holiday requires name', function (): void {
    CreateHoliday::assertValidationErrors([], 'name');
});

test('update holiday', function (): void {
    $holiday = CreateHoliday::make([
        'name' => 'Original',
        'date' => '2026-06-01',
        'day_part' => 'full_day',
        'is_recurring' => false,
    ])->validate()->execute();

    $updated = UpdateHoliday::make([
        'id' => $holiday->getKey(),
        'name' => 'Updated Holiday',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Holiday');
});

test('delete holiday', function (): void {
    $holiday = CreateHoliday::make([
        'name' => 'Temp',
        'date' => '2026-12-31',
        'day_part' => 'full_day',
        'is_recurring' => false,
    ])->validate()->execute();

    expect(DeleteHoliday::make(['id' => $holiday->getKey()])
        ->validate()->execute())->toBeTrue();
});
