<?php

use FluxErp\Actions\Commission\CreateCommission;
use FluxErp\Actions\Commission\DeleteCommission;
use FluxErp\Actions\Commission\UpdateCommission;
use FluxErp\Models\Commission;

test('create commission with inline rate', function (): void {
    $commission = CreateCommission::make([
        'user_id' => $this->user->getKey(),
        'commission_rate' => 0.05,
        'total_net_price' => 1000.00,
    ])->validate()->execute();

    expect($commission)->toBeInstanceOf(Commission::class);
});

test('create commission requires user_id', function (): void {
    CreateCommission::assertValidationErrors([
        'commission_rate' => 0.05,
        'total_net_price' => 500,
    ], 'user_id');
});

test('update commission', function (): void {
    $commission = CreateCommission::make([
        'user_id' => $this->user->getKey(),
        'commission_rate' => 0.05,
        'total_net_price' => 1000.00,
    ])->validate()->execute();

    $updated = UpdateCommission::make([
        'id' => $commission->getKey(),
        'commission' => $commission->commission,
    ])->validate()->execute();

    expect($updated)->not->toBeNull();
});

test('delete commission', function (): void {
    $commission = CreateCommission::make([
        'user_id' => $this->user->getKey(),
        'commission_rate' => 0.10,
        'total_net_price' => 500.00,
    ])->validate()->execute();

    expect(DeleteCommission::make(['id' => $commission->getKey()])
        ->validate()->execute())->toBeTrue();
});
