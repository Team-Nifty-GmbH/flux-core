<?php

use FluxErp\Actions\PaymentReminder\BundlePaymentReminders;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
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
        'invoice_number' => 'INV-2026-100',
        'balance' => 100,
        'payment_reminder_current_level' => 0,
        'payment_reminder_next_date' => now()->subDay()->toDateString(),
    ]);
});

test('bundle requires order_ids', function (): void {
    BundlePaymentReminders::assertValidationErrors([], 'order_ids');
});

test('failed send leaves no reminder record and logs activity on order', function (): void {
    $orderId = $this->order->getKey();
    $originalLevel = $this->order->payment_reminder_current_level;
    $originalNextDate = $this->order->payment_reminder_next_date?->toDateString();

    BundlePaymentReminders::make(['order_ids' => [$orderId]])
        ->validate()
        ->execute();

    expect(PaymentReminder::query()->where('order_id', $orderId)->count())->toBe(0);

    $order = Order::query()->whereKey($orderId)->first();
    expect($order->payment_reminder_current_level)->toBe($originalLevel);
    expect($order->payment_reminder_next_date?->toDateString())->toBe($originalNextDate);

    $failureLogged = Activity::query()
        ->where('subject_type', morph_alias(Order::class))
        ->where('subject_id', $orderId)
        ->where('event', 'payment_reminder_send_failed')
        ->exists();

    expect($failureLogged)->toBeTrue();
});
