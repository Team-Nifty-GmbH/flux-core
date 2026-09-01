<?php

use FluxErp\Actions\PaymentReminder\BundlePaymentReminders;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\SendPaymentReminderJob;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminderText;
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
        ->create([
            'is_direct_debit' => false,
        ]);

    $this->createOverdueOrder = function (string $invoiceNumber, int $currentLevel = 0) use (
        $orderType,
        $address,
        $contact,
        $paymentType
    ): Order {
        $order = Order::factory()->create([
            'order_type_id' => $orderType->getKey(),
            'address_invoice_id' => $address->getKey(),
            'contact_id' => $contact->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'price_list_id' => PriceList::factory()->create()->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'currency_id' => Currency::factory()->create()->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'is_locked' => true,
            'invoice_number' => $invoiceNumber,
            'balance' => 100,
            'payment_reminder_current_level' => $currentLevel,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ]);

        $order->update(['balance' => 100]);

        return $order;
    };

    $this->order = ($this->createOverdueOrder)('INV-2026-100');

    $this->configureLevel = fn (int $level, bool $withTemplate = true) => PaymentReminderText::factory()
        ->create([
            'reminder_level' => $level,
            'email_template_id' => $withTemplate ? EmailTemplate::factory()->create()->getKey() : null,
        ]);
});

test('bundle requires orders', function (): void {
    BundlePaymentReminders::assertValidationErrors([], 'orders');
});

test('dispatches a send job per eligible order', function (): void {
    Queue::fake();
    ($this->configureLevel)(1);

    $result = BundlePaymentReminders::make([
        'orders' => [
            ['id' => $this->order->getKey(), 'recipient' => null],
        ],
    ])
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
    ($this->configureLevel)(1);

    BundlePaymentReminders::make([
        'orders' => [
            ['id' => $this->order->getKey(), 'recipient' => 'override@example.com'],
        ],
    ])
        ->validate()
        ->execute();

    Queue::assertPushed(
        SendPaymentReminderJob::class,
        fn (SendPaymentReminderJob $job) => $job->recipientOverride === 'override@example.com'
    );
});

test('reports orders whose reminder level has no text instead of queueing them', function (): void {
    Queue::fake();

    $result = BundlePaymentReminders::make([
        'orders' => [
            ['id' => $this->order->getKey(), 'recipient' => null],
        ],
    ])
        ->validate()
        ->execute();

    expect($result['queued'])->toBe(0)
        ->and($result['unsendable'])->toHaveCount(1)
        ->and($result['unsendable'][0]['id'])->toBe($this->order->getKey())
        ->and($result['unsendable'][0]['reminder_level'])->toBe(1)
        ->and($result['unsendable'][0]['invoice_number'])->toBe('INV-2026-100')
        ->and($result['unsendable'][0]['reason'])->toBeString();

    Queue::assertNotPushed(SendPaymentReminderJob::class);
});

test('reports orders whose reminder text has no email template instead of queueing them', function (): void {
    Queue::fake();
    ($this->configureLevel)(1, withTemplate: false);

    $result = BundlePaymentReminders::make([
        'orders' => [
            ['id' => $this->order->getKey(), 'recipient' => null],
        ],
    ])
        ->validate()
        ->execute();

    expect($result['queued'])->toBe(0)
        ->and($result['unsendable'])->toHaveCount(1)
        ->and($result['unsendable'][0]['id'])->toBe($this->order->getKey());

    Queue::assertNotPushed(SendPaymentReminderJob::class);
});

test('queues the configured orders and reports only the unconfigured ones', function (): void {
    Queue::fake();
    ($this->configureLevel)(1);

    $unconfigured = ($this->createOverdueOrder)('INV-2026-101', currentLevel: 1);

    $result = BundlePaymentReminders::make([
        'orders' => [
            ['id' => $this->order->getKey(), 'recipient' => null],
            ['id' => $unconfigured->getKey(), 'recipient' => null],
        ],
    ])
        ->validate()
        ->execute();

    expect($result['queued'])->toBe(1)
        ->and($result['unsendable'])->toHaveCount(1)
        ->and($result['unsendable'][0]['id'])->toBe($unconfigured->getKey())
        ->and($result['unsendable'][0]['reminder_level'])->toBe(2);

    Queue::assertPushed(
        SendPaymentReminderJob::class,
        fn (SendPaymentReminderJob $job) => $job->orderId === $this->order->getKey()
    );
});
