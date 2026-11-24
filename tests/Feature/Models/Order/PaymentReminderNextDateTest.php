<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

test('first payment reminder date counts from the payment target date', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'invoice_number' => null,
        'invoice_date' => null,
        'payment_target' => 5,
        'payment_reminder_days_1' => 5,
        'payment_reminder_next_date' => null,
    ]);

    $order->invoice_date = '2026-11-10';
    $order->invoice_number = 'INV-' . uniqid();
    $order->save();

    expect($order->refresh())
        ->payment_target_date->toDateString()->toBe('2026-11-15')
        ->payment_reminder_next_date->toDateString()->toBe('2026-11-20')
        ->invoice_date->toDateString()->toBe('2026-11-10');
});
