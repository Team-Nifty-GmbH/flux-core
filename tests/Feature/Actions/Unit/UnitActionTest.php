<?php

use FluxErp\Actions\Unit\CreateUnit;
use FluxErp\Actions\Unit\DeleteUnit;
use FluxErp\Actions\Unit\UpdateUnit;
use FluxErp\Models\Unit;

test('create unit', function (): void {
    $unit = CreateUnit::make([
        'name' => 'Kilogramm',
        'abbreviation' => 'kg',
    ])->validate()->execute();

    expect($unit)
        ->toBeInstanceOf(Unit::class)
        ->name->toBe('Kilogramm')
        ->abbreviation->toBe('kg');
});

test('create unit requires name', function (): void {
    CreateUnit::assertValidationErrors(
        ['abbreviation' => 'kg'],
        'name'
    );
});

test('create unit requires abbreviation', function (): void {
    CreateUnit::assertValidationErrors(
        ['name' => 'Kilogramm'],
        'abbreviation'
    );
});

test('update unit', function (): void {
    $unit = Unit::factory()->create();

    $updated = UpdateUnit::make([
        'id' => $unit->getKey(),
        'name' => 'Meter',
    ])->validate()->execute();

    expect($updated)
        ->toBeInstanceOf(Unit::class)
        ->name->toBe('Meter');
});

test('update unit requires id', function (): void {
    UpdateUnit::assertValidationErrors(
        ['name' => 'Meter'],
        'id'
    );
});

test('delete unit', function (): void {
    $unit = Unit::factory()->create();

    $result = DeleteUnit::make([
        'id' => $unit->getKey(),
    ])->validate()->execute();

    expect($result)->toBeTrue();
    expect(Unit::query()->whereKey($unit->getKey())->exists())->toBeFalse();
});

test('delete unit with invalid id fails validation', function (): void {
    DeleteUnit::assertValidationErrors(
        ['id' => 999999],
        'id'
    );
});
