<?php

use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Actions\SerialNumber\DeleteSerialNumber;
use FluxErp\Actions\SerialNumber\UpdateSerialNumber;
use FluxErp\Models\SerialNumber;

test('create serial number', function (): void {
    $sn = CreateSerialNumber::make([
        'serial_number' => 'SN-2026-001',
    ])->validate()->execute();

    expect($sn)->toBeInstanceOf(SerialNumber::class)
        ->serial_number->toBe('SN-2026-001');
});

test('create serial number requires serial_number', function (): void {
    CreateSerialNumber::assertValidationErrors([], 'serial_number');
});

test('update serial number', function (): void {
    $sn = SerialNumber::factory()->create();

    $updated = UpdateSerialNumber::make([
        'id' => $sn->getKey(),
        'serial_number' => 'SN-UPDATED',
    ])->validate()->execute();

    expect($updated->serial_number)->toBe('SN-UPDATED');
});

test('delete serial number', function (): void {
    $sn = SerialNumber::factory()->create();

    expect(DeleteSerialNumber::make(['id' => $sn->getKey()])
        ->validate()->execute())->toBeTrue();
});
