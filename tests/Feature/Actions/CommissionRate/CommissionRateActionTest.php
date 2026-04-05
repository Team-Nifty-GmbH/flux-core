<?php

use FluxErp\Actions\CommissionRate\CreateCommissionRate;
use FluxErp\Actions\CommissionRate\DeleteCommissionRate;
use FluxErp\Actions\CommissionRate\UpdateCommissionRate;
use FluxErp\Models\CommissionRate;

test('create commission rate', function (): void {
    $rate = CreateCommissionRate::make([
        'user_id' => $this->user->getKey(),
        'commission_rate' => 0.05,
    ])->validate()->execute();

    expect($rate)->toBeInstanceOf(CommissionRate::class);
});

test('create commission rate requires user_id and rate', function (): void {
    CreateCommissionRate::assertValidationErrors([], ['user_id', 'commission_rate']);
});

test('update commission rate', function (): void {
    $rate = CommissionRate::factory()->create([
        'user_id' => $this->user->getKey(),
    ]);

    $updated = UpdateCommissionRate::make([
        'id' => $rate->getKey(),
        'commission_rate' => 0.10,
    ])->validate()->execute();

    expect($updated->commission_rate)->toEqual(0.10);
});

test('delete commission rate', function (): void {
    $rate = CommissionRate::factory()->create([
        'user_id' => $this->user->getKey(),
    ]);

    expect(DeleteCommissionRate::make(['id' => $rate->getKey()])
        ->validate()->execute())->toBeTrue();
});
