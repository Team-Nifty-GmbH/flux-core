<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $this->order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $this->transaction = Transaction::factory()->create([
        'amount' => -100,
        'balance' => -100,
        'contact_bank_connection_id' => null,
    ]);
});

test('deleting an accepted assignment gives the amount back to the balance', function (): void {
    $assignment = OrderTransaction::query()->create([
        'order_id' => $this->order->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -100,
        'is_accepted' => true,
    ]);

    expect($this->transaction->refresh()->balance)->toEqual(0);

    $assignment->delete();

    expect($this->transaction->refresh()->balance)->toEqual(-100);
});

test('an unaccepted assignment does not count towards the balance', function (): void {
    $assignment = OrderTransaction::query()->create([
        'order_id' => $this->order->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -100,
        'is_accepted' => false,
    ]);

    expect($this->transaction->calculateBalance()->balance)->toEqual(-100);

    $assignment->delete();

    expect($this->transaction->refresh()->balance)->toEqual(-100);
});

test('taking the acceptance back gives the amount back to the balance', function (): void {
    $assignment = OrderTransaction::query()->create([
        'order_id' => $this->order->getKey(),
        'transaction_id' => $this->transaction->getKey(),
        'amount' => -100,
        'is_accepted' => true,
    ]);

    expect($this->transaction->refresh()->balance)->toEqual(0);

    $assignment->update(['is_accepted' => false]);

    expect($this->transaction->refresh()->balance)->toEqual(-100);
});
