<?php

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;

test('create serial number range', function (): void {
    $range = CreateSerialNumberRange::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'model_type' => morph_alias(FluxErp\Models\Order::class),
        'type' => 'order_number',
        'start_number' => 1000,
    ])->validate()->execute();

    expect($range)
        ->type->toBe('order_number')
        ->current_number->toBe(999); // start_number is decremented
});

test('create serial number range requires tenant model_type and type', function (): void {
    CreateSerialNumberRange::assertValidationErrors([], ['tenant_id', 'model_type', 'type']);
});

test('update serial number range', function (): void {
    $range = CreateSerialNumberRange::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'model_type' => morph_alias(FluxErp\Models\Order::class),
        'type' => 'order_number',
        'start_number' => 1000,
    ])->validate()->execute();

    $updated = UpdateSerialNumberRange::make([
        'id' => $range->getKey(),
        'prefix' => 'ORD-',
    ])->validate()->execute();

    expect($updated->prefix)->toBe('ORD-');
});

test('delete serial number range', function (): void {
    $range = CreateSerialNumberRange::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'model_type' => morph_alias(FluxErp\Models\Order::class),
        'type' => 'temp_number',
        'start_number' => 1,
    ])->validate()->execute();

    expect(DeleteSerialNumberRange::make(['id' => $range->getKey()])
        ->validate()->execute())->toBeTrue();
});
