<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;

test('can create new order', function (): void {
    OrderType::query()->delete();

    $orderType = OrderType::factory()
        ->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
            'is_hidden' => false,
        ]);

    $address = Address::factory()
        ->for(
            Contact::factory()
        )
        ->create([
            'company' => 'Test Company ' . uniqid(),
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'is_main_address' => true,
            'is_delivery_address' => true,
            'is_invoice_address' => true,
        ]);

    $page = visit(route('orders.orders'))
        ->assertRoute('orders.orders')
        ->assertNoSmoke()
        ->assertSee('New order')
        ->click('New order')
        ->assertSee('Order type')
        ->click($this->tsSelect('order.order_type_id'))
        ->assertSee($orderType->name)
        ->click($this->tsSelectOption($orderType->name))
        ->click($this->tsSelect('order.contact_id'))
        ->assertSee($address->name)
        ->click($this->tsSelectOption($address->name))
        ->assertNoSmoke()
        ->click('Save');

    $order = Order::query()->latest('id')->first();
    expect($order)->not->toBeNull();
    expect($order->contact_id)->toBe($address->contact_id);

    $page->visit(route('orders.id', ['id' => $order->getKey()]))
        ->assertNoSmoke()
        ->assertSee($orderType->name . ' ' . $order->order_number)
        ->assertSee('Contact')
        ->assertSee('Invoice Address')
        ->assertSee('Delivery Address')
        ->assertRoute('orders.id', ['id' => $order->getKey()]);
});
