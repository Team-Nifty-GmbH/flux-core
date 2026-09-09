<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);
    $this->contact = $contact;

    $this->makeDueOrder = function (string $paymentState): Order {
        $order = Order::factory()->create([
            'order_type_id' => $this->orderType->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'contact_id' => $this->contact->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'price_list_id' => PriceList::factory()->create()->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'currency_id' => Currency::factory()->create()->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'is_locked' => true,
            'invoice_number' => 'INV-' . uniqid(),
            'balance' => 100,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ]);

        // Bypass model recalculation/state machine to force the values under test.
        Order::query()->whereKey($order->getKey())->update([
            'payment_state' => $paymentState,
            'balance' => 100,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ]);

        return $order->refresh();
    };
});

test('paid orders are excluded from the dunning run even with a non-zero balance', function (): void {
    $paid = ($this->makeDueOrder)('paid');

    $dueIds = resolve_static(Order::class, 'query')
        ->wherePaymentReminderDue()
        ->pluck('id');

    expect($dueIds)->not->toContain($paid->getKey());
});

test('orders in payment or in a payment run are excluded from the dunning run', function (): void {
    $inPayment = ($this->makeDueOrder)('in_payment');
    $inRun = ($this->makeDueOrder)('in_open_payment_run');

    $dueIds = resolve_static(Order::class, 'query')
        ->wherePaymentReminderDue()
        ->pluck('id');

    expect($dueIds)->not->toContain($inPayment->getKey())
        ->and($dueIds)->not->toContain($inRun->getKey());
});

test('open and partial paid orders remain in the dunning run', function (): void {
    $open = ($this->makeDueOrder)('open');
    $partial = ($this->makeDueOrder)('partial_paid');

    $dueIds = resolve_static(Order::class, 'query')
        ->wherePaymentReminderDue()
        ->pluck('id');

    expect($dueIds)->toContain($open->getKey())
        ->and($dueIds)->toContain($partial->getKey());
});

test('direct debit orders are excluded until a debit has failed', function (): void {
    $directDebitPaymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => true,
        ]);

    $order = ($this->makeDueOrder)('open');
    $order->update(['payment_type_id' => $directDebitPaymentType->getKey()]);

    expect(Order::query()->wherePaymentReminderDue()->whereKey($order->getKey())->exists())->toBeFalse();
});

test('direct debit orders with a failed debit run are included', function (): void {
    $directDebitPaymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => true,
        ]);

    $order = ($this->makeDueOrder)('open');
    $order->update(['payment_type_id' => $directDebitPaymentType->getKey()]);

    $failedRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'direct_debit',
        'state' => 'not_successful',
    ]);
    $order->paymentRuns()->attach($failedRun->getKey(), ['amount' => 100]);

    expect(Order::query()->wherePaymentReminderDue()->whereKey($order->getKey())->exists())->toBeTrue();
});

test('direct debit orders with only successful debit runs stay excluded', function (): void {
    $directDebitPaymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => true,
        ]);

    $order = ($this->makeDueOrder)('open');
    $order->update(['payment_type_id' => $directDebitPaymentType->getKey()]);

    $successfulRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'direct_debit',
        'state' => 'successful',
    ]);
    $order->paymentRuns()->attach($successfulRun->getKey(), ['amount' => 100]);

    expect(Order::query()->wherePaymentReminderDue()->whereKey($order->getKey())->exists())->toBeFalse();
});
