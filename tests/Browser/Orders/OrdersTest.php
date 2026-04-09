<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
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
        ->assertNoSmoke();

    waitForElement($page, '[tall-datatable] [wire\\:id]');

    $page->click('New order');

    // assertSee('Order type') would match the DataTable column header,
    // so wait for the actual select element inside the modal instead
    waitForElement($page, 'div[x-data*="order.order_type_id"] button[x-ref="button"]', 15000);

    $page->click($this->tsSelect('order.order_type_id'))
        ->assertSee($orderType->name)
        ->click($this->tsSelectOption($orderType->name));

    $page->click($this->tsSelect('order.contact_id'))
        ->assertSee($address->name)
        ->click($this->tsSelectOption($address->name))
        ->assertNoSmoke()
        ->click('Save')
        ->assertNoSmoke()
        ->assertSee($orderType->name)
        ->assertSee('Contact')
        ->assertSee('Invoice Address')
        ->assertSee('Delivery Address');
});
