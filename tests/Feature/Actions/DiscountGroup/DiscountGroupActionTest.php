<?php

use FluxErp\Actions\DiscountGroup\CreateDiscountGroup;
use FluxErp\Actions\DiscountGroup\DeleteDiscountGroup;
use FluxErp\Actions\DiscountGroup\UpdateDiscountGroup;

test('create discount group', function (): void {
    $group = CreateDiscountGroup::make(['name' => 'VIP Discounts'])
        ->validate()->execute();

    expect($group)->name->toBe('VIP Discounts');
});

test('create discount group requires name', function (): void {
    CreateDiscountGroup::assertValidationErrors([], 'name');
});

test('update discount group', function (): void {
    $group = CreateDiscountGroup::make(['name' => 'Original'])
        ->validate()->execute();

    $updated = UpdateDiscountGroup::make([
        'id' => $group->getKey(),
        'name' => 'Premium',
    ])->validate()->execute();

    expect($updated->name)->toBe('Premium');
});

test('delete discount group', function (): void {
    $group = CreateDiscountGroup::make(['name' => 'Temp'])
        ->validate()->execute();

    expect(DeleteDiscountGroup::make(['id' => $group->getKey()])
        ->validate()->execute())->toBeTrue();
});
