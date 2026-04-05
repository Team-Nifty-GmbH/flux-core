<?php

use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\PaymentReminder\DeletePaymentReminder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $this->order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-001',
    ]);
});

test('create payment reminder for locked order', function (): void {
    $reminder = CreatePaymentReminder::make([
        'order_id' => $this->order->getKey(),
        'reminder_level' => 1,
    ])->validate()->execute();

    expect($reminder)->order_id->toBe($this->order->getKey());
});

test('create payment reminder requires order_id', function (): void {
    CreatePaymentReminder::assertValidationErrors([], 'order_id');
});

test('create payment reminder fails for unlocked order', function (): void {
    $this->order->update(['is_locked' => false]);

    CreatePaymentReminder::assertValidationErrors([
        'order_id' => $this->order->getKey(),
    ], 'order_id');
});

test('delete payment reminder', function (): void {
    $reminder = CreatePaymentReminder::make([
        'order_id' => $this->order->getKey(),
        'reminder_level' => 1,
    ])->validate()->execute();

    expect(DeletePaymentReminder::make(['id' => $reminder->getKey()])
        ->validate()->execute())->toBeTrue();
});
