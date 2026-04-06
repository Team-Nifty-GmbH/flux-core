<?php

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\Discount\DeleteDiscount;
use FluxErp\Models\Discount;

test('create discount fixed amount', function (): void {
    $discount = CreateDiscount::make([
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => FluxErp\Models\Contact::factory()->create()->getKey(),
        'discount' => 10.00,
        'is_percentage' => false,
    ])->validate()->execute();

    expect($discount)
        ->toBeInstanceOf(Discount::class)
        ->discount->toEqual(10.00);
});

test('create discount percentage', function (): void {
    $discount = CreateDiscount::make([
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => FluxErp\Models\Contact::factory()->create()->getKey(),
        'discount' => 0.15,
        'is_percentage' => true,
    ])->validate()->execute();

    expect($discount->is_percentage)->toBeTruthy();
});

test('percentage discount must be <= 1', function (): void {
    CreateDiscount::assertValidationErrors([
        'discount' => 1.5,
        'is_percentage' => true,
    ], 'discount');
});

test('create discount requires discount and is_percentage', function (): void {
    CreateDiscount::assertValidationErrors([], ['discount', 'is_percentage']);
});

test('delete discount', function (): void {
    $contact = FluxErp\Models\Contact::factory()->create();
    $discount = CreateDiscount::make([
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => $contact->getKey(),
        'discount' => 5.00,
        'is_percentage' => false,
    ])->validate()->execute();

    expect(DeleteDiscount::make(['id' => $discount->getKey()])
        ->validate()->execute())->toBeTrue();
});
