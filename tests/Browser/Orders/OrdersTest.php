<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use Illuminate\Support\Str;

test('can create new order', function (): void {
    $orderType = OrderType::factory()
        ->recycle($this->dbClient)
        ->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_hidden' => false,
        ]);

    $address = Address::factory()
        ->recycle($this->dbClient)
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
        ->click('//div[contains(@x-data, "order.order_type_id")]//button[@x-ref="button"]')
        ->assertSee($orderType->name)
        ->click('//li[@role="option"][contains(., "' . $orderType->name . '")]')
        ->click('//div[contains(@x-data, "order.contact_id")]//button[@x-ref="button"]')
        ->assertSee($address->name)
        ->click('//li[@role="option"][contains(., "' . $address->name . '")]')
        ->click('Save')
        ->assertSee('Order positions');

    $order = Order::query()
        ->whereKey(Str::afterLast($page->url(), '/'))
        ->first();

    $page->assertSee($orderType->name . ' ' . $order->order_number)
        ->assertSee('Contact')
        ->assertSee('Invoice Address')
        ->assertSee('Delivery Address')
        ->assertRoute('orders.id', ['id' => $order->getKey()]);
});
