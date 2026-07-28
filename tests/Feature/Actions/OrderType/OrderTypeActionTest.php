<?php

use FluxErp\Actions\OrderType\CreateOrderType;
use FluxErp\Actions\OrderType\DeleteOrderType;
use FluxErp\Actions\OrderType\UpdateOrderType;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Validation\ValidationException;

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

test('order type in use cannot be deleted', function (): void {
    $type = OrderType::factory()->create();
    $contact = Contact::factory()->create();
    Order::factory()->create([
        'order_type_id' => $type->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => Address::factory()->create([
            'contact_id' => $contact->getKey(),
            'is_main_address' => true,
            'is_invoice_address' => true,
        ])->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'payment_type_id' => PaymentType::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    // Deleting would leave the order without the type it was created with, so
    // the type has to be merged into another one first.
    DeleteOrderType::make(['id' => $type->getKey()])->validate()->execute();
})->throws(ValidationException::class);

test('order type without orders can still be deleted', function (): void {
    $type = OrderType::factory()->create();

    expect(DeleteOrderType::make(['id' => $type->getKey()])
        ->validate()->execute())->toBeTrue();
});
