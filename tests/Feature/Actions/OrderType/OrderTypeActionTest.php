<?php

use FluxErp\Actions\OrderType\CreateOrderType;
use FluxErp\Actions\OrderType\DeleteOrderType;
use FluxErp\Actions\OrderType\UpdateOrderType;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;

test('create order type', function (): void {
    $type = CreateOrderType::make([
        'name' => 'Invoice',
        'order_type_enum' => OrderTypeEnum::Order->value,
    ])->validate()->execute();

    expect($type)->toBeInstanceOf(OrderType::class)
        ->name->toBe('Invoice');
});

test('create order type requires name and enum', function (): void {
    CreateOrderType::assertValidationErrors([], ['name', 'order_type_enum']);
});

test('update order type', function (): void {
    $type = OrderType::factory()->create();

    $updated = UpdateOrderType::make([
        'id' => $type->getKey(),
        'name' => 'Credit Note',
    ])->validate()->execute();

    expect($updated->name)->toBe('Credit Note');
});

test('delete order type', function (): void {
    $type = OrderType::factory()->create();

    expect(DeleteOrderType::make(['id' => $type->getKey()])
        ->validate()->execute())->toBeTrue();
});
