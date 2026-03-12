<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\MatchTransactionsWithOrderJob;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Transaction;
use FluxErp\Settings\AccountingSettings;

beforeEach(function (): void {
    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => true,
        'auto_send_reminders' => false,
    ]);

    $this->contact = Contact::factory()
        ->state(['tenant_id' => $this->dbTenant->getKey()])
        ->create();

    $this->address = Address::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->getKey(),
        ])
        ->for($this->contact, 'contact')
        ->create();

    $this->contact->update([
        'main_address_id' => $this->address->getKey(),
    ]);

    $this->orderType = OrderType::factory()
        ->state([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])
        ->for(factory: $this->dbTenant, relationship: 'tenant')
        ->create();

    $this->iban = fake()->iban();

    ContactBankConnection::factory()
        ->state([
            'contact_id' => $this->contact->getKey(),
            'iban' => $this->iban,
        ])
        ->create();

    SerialNumberRange::factory()
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'type' => 'invoice_number',
            'model_type' => morph_alias(Order::class),
            'model_id' => null,
            'prefix' => 'TNRRe-',
            'suffix' => null,
            'length' => 4,
            'current_number' => 1700,
        ])
        ->create();
});

test('does not match already paid orders by partial invoice number and total gross price', function (): void {
    $paidOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'invoice_number' => 'TNRRe-0042',
            'invoice_date' => now()->subMonth(),
            'is_locked' => true,
            'total_gross_price' => 60.69,
            'balance' => 0,
        ])
        ->create();

    $openOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'invoice_number' => 'TNRRe-1608',
            'invoice_date' => now()->subDay(),
            'is_locked' => true,
            'total_gross_price' => 60.69,
            'balance' => 60.69,
        ])
        ->create();

    $openOrder->update(['balance' => 60.69]);

    $transaction = Transaction::factory()
        ->state([
            'amount' => 60.69,
            'balance' => 60.69,
            'booking_date' => now(),
            'value_date' => now(),
            'purpose' => 'TNRRe-1608 vom 28.02.2026 EREF: TNRRe-1608',
            'counterpart_iban' => $this->iban,
            'counterpart_name' => $this->contact->main_address?->name ?? 'Test',
            'is_ignored' => false,
        ])
        ->create();

    $job = new MatchTransactionsWithOrderJob([$transaction->getKey()]);
    $job->handle();

    $assignments = OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->get();

    // Should only match the open order, not the paid one
    expect($assignments)->toHaveCount(1);
    expect($assignments->first()->order_id)->toBe($openOrder->getKey());
    expect((float) $assignments->first()->amount)->toBe(60.69);
    expect($assignments->first()->is_accepted)->toBeTrue();
});

test('extracts invoice numbers using serial number range pattern instead of blind word splitting', function (): void {
    // Create multiple paid orders with same amount and matching prefix
    foreach (['TNRRe-0042', 'TNRRe-0692', 'TNRRe-1227'] as $invoiceNumber) {
        Order::factory()
            ->for(Currency::factory(), 'currency')
            ->for(Language::factory(), 'language')
            ->for(PriceList::factory(), 'priceList')
            ->for(PaymentType::factory(), 'paymentType')
            ->for($this->orderType, 'orderType')
            ->state([
                'tenant_id' => $this->dbTenant->getKey(),
                'contact_id' => $this->contact->getKey(),
                'address_invoice_id' => $this->address->getKey(),
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->subMonths(3),
                'is_locked' => true,
                'total_gross_price' => 60.69,
                'balance' => 0,
            ])
            ->create();
    }

    // Create the open order that should be the only match
    $openOrder = Order::factory()
        ->for(Currency::factory(), 'currency')
        ->for(Language::factory(), 'language')
        ->for(PriceList::factory(), 'priceList')
        ->for(PaymentType::factory(), 'paymentType')
        ->for($this->orderType, 'orderType')
        ->state([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'invoice_number' => 'TNRRe-1608',
            'invoice_date' => now()->subDay(),
            'is_locked' => true,
            'total_gross_price' => 60.69,
            'balance' => 60.69,
        ])
        ->create();

    $openOrder->update(['balance' => 60.69]);

    $transaction = Transaction::factory()
        ->state([
            'amount' => 60.69,
            'balance' => 60.69,
            'booking_date' => now(),
            'value_date' => now(),
            'purpose' => 'TNRRe-1608 vom 28.02.2026 TAN1:SecureGo plus EREF: TNRRe-1608 MREF: 34',
            'counterpart_iban' => $this->iban,
            'counterpart_name' => $this->contact->main_address?->name ?? 'Test',
            'is_ignored' => false,
        ])
        ->create();

    $job = new MatchTransactionsWithOrderJob([$transaction->getKey()]);
    $job->handle();

    $assignments = OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->get();

    // Word "TNRRe" from splitting by "-" must NOT match all orders with that prefix
    // Only the exact invoice number TNRRe-1608 should be matched
    expect($assignments)->toHaveCount(1);
    expect($assignments->first()->order_id)->toBe($openOrder->getKey());
});
