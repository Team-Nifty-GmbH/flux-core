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

test('create discount at a given position moves the others down', function (): void {
    $contact = FluxErp\Models\Contact::factory()->create();
    $attributes = [
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => $contact->getKey(),
        'is_percentage' => true,
    ];
    $first = CreateDiscount::make(array_merge($attributes, ['discount' => 0.1]))->validate()->execute();
    $second = CreateDiscount::make(array_merge($attributes, ['discount' => 0.2]))->validate()->execute();

    $inserted = CreateDiscount::make(array_merge($attributes, ['discount' => 0.3, 'order_column' => 1]))
        ->validate()
        ->execute();

    expect(Discount::query()->where('model_id', $contact->getKey())->ordered()->pluck('id')->all())
        ->toBe([$inserted->getKey(), $first->getKey(), $second->getKey()]);
});
