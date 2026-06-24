<?php

use FluxErp\Actions\PaymentReminder\BundlePaymentReminders;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\SendPaymentReminderJob;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Support\Facades\Queue;

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

    $this->order->update(['balance' => 100]);
});

test('bundle requires order_ids', function (): void {
    BundlePaymentReminders::assertValidationErrors([], 'order_ids');
});

test('dispatches a send job per eligible order', function (): void {
    Queue::fake();

    $result = BundlePaymentReminders::make(['order_ids' => [$this->order->getKey()]])
        ->validate()
        ->execute();

    expect($result['queued'])->toBe(1);

    Queue::assertPushed(
        SendPaymentReminderJob::class,
        fn (SendPaymentReminderJob $job) => $job->orderId === $this->order->getKey()
    );
});

test('passes the recipient override to the job', function (): void {
    Queue::fake();

    BundlePaymentReminders::make([
        'order_ids' => [$this->order->getKey()],
        'recipients' => [$this->order->contact_id . '-1' => 'override@example.com'],
    ])
        ->validate()
        ->execute();

    Queue::assertPushed(
        SendPaymentReminderJob::class,
        fn (SendPaymentReminderJob $job) => $job->recipientOverride === 'override@example.com'
    );
});
