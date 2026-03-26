<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\AutoSendPaymentRemindersJob;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Settings\AccountingSettings;
use Illuminate\Support\Str;

beforeEach(function (): void {
    AccountingSettings::fake([
        'auto_send_reminders' => true,
        'auto_accept_secure_transaction_matches' => false,
    ]);

    $this->contact = Contact::factory()
        ->state(['tenant_id' => $this->dbTenant->getKey()])
        ->create();

    $this->address = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => 'test@example.com',
        ])
        ->for($this->contact, 'contact')
        ->create();

    $this->contact->update([
        'main_address_id' => $this->address->id,
    ]);

    $this->orderType = OrderType::factory()
        ->state([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])
        ->for(factory: $this->dbTenant, relationship: 'tenant')
        ->create();
});

test('sends payment reminders for overdue orders', function (): void {
    $overdueOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
            'payment_reminder_days_1' => 14,
            'payment_reminder_days_2' => 14,
            'payment_reminder_days_3' => 14,
        ])
        ->create();

    // Force balance update after creation (it gets reset by observers)
    $overdueOrder->update(['balance' => 100.00]);

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $overdueOrder->id)->exists())->toBeTrue();
});

test('does not send payment reminders when setting is disabled', function (): void {
    AccountingSettings::fake([
        'auto_send_reminders' => false,
        'auto_accept_secure_transaction_matches' => false,
    ]);

    $overdueOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ])
        ->create();

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $overdueOrder->id)->exists())->toBeFalse();
});

test('does not send payment reminders for orders without invoice number', function (): void {
    $orderWithoutInvoiceNumber = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => null,
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ])
        ->create();

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $orderWithoutInvoiceNumber->id)->exists())->toBeFalse();
});

test('does not send payment reminders for orders with zero balance', function (): void {
    $paidOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 0,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ])
        ->create();

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $paidOrder->id)->exists())->toBeFalse();
});

test('does not send payment reminders for not yet due orders', function (): void {
    $notYetDueOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->addDay()->toDateString(),
        ])
        ->create();

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $notYetDueOrder->id)->exists())->toBeFalse();
});

test('does not send payment reminders for purchase orders', function (): void {
    $purchaseOrderType = OrderType::factory()
        ->state([
            'order_type_enum' => OrderTypeEnum::Purchase,
            'is_active' => true,
        ])
        ->for(factory: $this->dbTenant, relationship: 'tenant')
        ->create();

    $purchaseOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($purchaseOrderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ])
        ->create();

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $purchaseOrder->id)->exists())->toBeFalse();
});

test('does not send payment reminders for orders at maximum reminder level', function (): void {
    $maxLevelOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 3,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ])
        ->create();

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $maxLevelOrder->id)->exists())->toBeFalse();
});

test('falls back to contact invoice address when order address has no email', function (): void {
    $addressWithoutEmail = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => null,
            'is_main_address' => false,
            'is_invoice_address' => false,
            'is_delivery_address' => false,
        ])
        ->for($this->contact, 'contact')
        ->create();

    $contactInvoiceAddress = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => 'invoice@example.com',
            'is_main_address' => false,
            'is_invoice_address' => false,
            'is_delivery_address' => false,
        ])
        ->for($this->contact, 'contact')
        ->create();

    $this->contact->update([
        'invoice_address_id' => $contactInvoiceAddress->id,
    ]);

    $order = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $addressWithoutEmail->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
            'payment_reminder_days_1' => 14,
            'payment_reminder_days_2' => 14,
            'payment_reminder_days_3' => 14,
        ])
        ->create();

    $order->update(['balance' => 100.00]);

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $order->id)->exists())->toBeTrue();
});

test('falls back to contact main address when no invoice addresses have email', function (): void {
    $addressWithoutEmail = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => null,
            'is_main_address' => false,
            'is_invoice_address' => false,
            'is_delivery_address' => false,
        ])
        ->for($this->contact, 'contact')
        ->create();

    $order = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $addressWithoutEmail->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
            'payment_reminder_days_1' => 14,
            'payment_reminder_days_2' => 14,
            'payment_reminder_days_3' => 14,
        ])
        ->create();

    $order->update(['balance' => 100.00]);

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $order->id)->exists())->toBeTrue();
});

test('does not send payment reminders when no address has email', function (): void {
    $addressWithoutEmail = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => null,
            'is_main_address' => false,
            'is_invoice_address' => false,
            'is_delivery_address' => false,
        ])
        ->for($this->contact, 'contact')
        ->create();

    $this->contact->update([
        'main_address_id' => $addressWithoutEmail->id,
    ]);

    $this->address->update(['email_primary' => null]);

    $order = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $addressWithoutEmail->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
        ])
        ->create();

    $job = new AutoSendPaymentRemindersJob();
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $order->id)->exists())->toBeFalse();
});

test('resolveMailableInvoiceAddress prefers order invoice address', function (): void {
    $order = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
        ])
        ->create();

    $order->load(['addressInvoice', 'contact.mainAddress', 'contact.invoiceAddress']);

    expect($order->resolveMailableInvoiceAddress()->getKey())->toBe($this->address->getKey());
});

test('resolveMailableInvoiceAddress falls back to contact invoice address', function (): void {
    $addressWithoutEmail = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => null,
            'is_main_address' => false,
            'is_invoice_address' => false,
            'is_delivery_address' => false,
        ])
        ->for($this->contact, 'contact')
        ->create();

    $contactInvoiceAddress = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => 'invoice@example.com',
            'is_main_address' => false,
            'is_invoice_address' => false,
            'is_delivery_address' => false,
        ])
        ->for($this->contact, 'contact')
        ->create();

    $this->contact->update([
        'invoice_address_id' => $contactInvoiceAddress->id,
    ]);

    $order = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $addressWithoutEmail->id,
        ])
        ->create();

    $order->load(['addressInvoice', 'contact.mainAddress', 'contact.invoiceAddress']);

    expect($order->resolveMailableInvoiceAddress()->getKey())->toBe($contactInvoiceAddress->getKey());
});

test('resolveMailableInvoiceAddress falls back to contact main address', function (): void {
    $addressWithoutEmail = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'email_primary' => null,
            'is_main_address' => false,
            'is_invoice_address' => false,
            'is_delivery_address' => false,
        ])
        ->for($this->contact, 'contact')
        ->create();

    $order = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $addressWithoutEmail->id,
        ])
        ->create();

    $order->load(['addressInvoice', 'contact.mainAddress', 'contact.invoiceAddress']);

    expect($order->resolveMailableInvoiceAddress()->getKey())->toBe($this->address->getKey());
});

test('processes only specified order ids when provided', function (): void {
    $order1 = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
            'payment_reminder_days_1' => 14,
            'payment_reminder_days_2' => 14,
            'payment_reminder_days_3' => 14,
        ])
        ->create();

    $order2 = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'invoice_number' => Str::uuid(),
            'is_locked' => true,
            'balance' => 100.00,
            'payment_reminder_current_level' => 0,
            'payment_reminder_next_date' => now()->subDay()->toDateString(),
            'payment_reminder_days_1' => 14,
            'payment_reminder_days_2' => 14,
            'payment_reminder_days_3' => 14,
        ])
        ->create();

    // Force balance update after creation (it gets reset by observers)
    $order1->update(['balance' => 100.00]);
    $order2->update(['balance' => 100.00]);

    $job = new AutoSendPaymentRemindersJob([$order1->id]);
    $job->handle();

    expect(PaymentReminder::query()->where('order_id', $order1->id)->exists())->toBeTrue();
    expect(PaymentReminder::query()->where('order_id', $order2->id)->exists())->toBeFalse();
});
