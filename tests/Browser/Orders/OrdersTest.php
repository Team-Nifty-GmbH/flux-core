<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderType;

test('can create new order', function (): void {
    grantModelPermissions($this->user);

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

    $page->script("() => new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('Livewire not ready')), 10000);
        const check = () => {
            if (window.Livewire) {
                clearTimeout(timeout);
                resolve();
            } else {
                setTimeout(check, 200);
            }
        };
        check();
    })");

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

test('create dialog clears the delivery address as soon as a purchase type is picked', function (): void {
    OrderType::query()->delete();

    OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
        'name' => 'Sales ' . uniqid(),
    ]);
    $purchaseType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
        'is_hidden' => false,
        'name' => 'Purchase ' . uniqid(),
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()
        ->for($contact)
        ->create([
            'company' => 'Test Company ' . uniqid(),
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'is_main_address' => true,
            'is_delivery_address' => true,
            'is_invoice_address' => true,
        ]);
    $contact->update([
        'delivery_address_id' => $address->getKey(),
        'invoice_address_id' => $address->getKey(),
        'main_address_id' => $address->getKey(),
    ]);

    $waitForDeliveryAddress = fn (string $condition): string => "() => new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('Delivery address did not change')), 10000);
        const check = () => {
            const value = Livewire.all()
                .map((component) => component.\$wire.order?.address_delivery_id)
                .find((id) => id !== undefined);
            const select = document.querySelector('#delivery-address-id').innerText;
            if ({$condition}) {
                clearTimeout(timeout);
                resolve();
            } else {
                setTimeout(check, 200);
            }
        };
        check();
    })";

    $page = visit(route('orders.orders'))
        ->assertRoute('orders.orders')
        ->assertNoSmoke();

    $page->script("() => new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('Livewire not ready')), 10000);
        const check = () => {
            if (window.Livewire) {
                clearTimeout(timeout);
                resolve();
            } else {
                setTimeout(check, 200);
            }
        };
        check();
    })");

    $page->click('New order');

    waitForElement($page, 'div[x-data*="order.order_type_id"] button[x-ref="button"]', 15000);

    $page->click($this->tsSelect('order.contact_id'))
        ->assertSee($address->name)
        ->click($this->tsSelectOption($address->name));

    $page->script($waitForDeliveryAddress(
        "value === {$address->getKey()} && select.includes('{$address->company}')"
    ));

    $page->click($this->tsSelect('order.order_type_id'))
        ->assertSee($purchaseType->name)
        ->click($this->tsSelectOption($purchaseType->name));

    $page->script($waitForDeliveryAddress("value === null && ! select.includes('{$address->company}')"));
});
