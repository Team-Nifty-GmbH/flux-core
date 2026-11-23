<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Livewire\Accounting\MoneyTransfer;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use FluxErp\States\Order\PaymentState\Overpaid;
use FluxErp\States\Order\PaymentState\PartialPaid;
use Livewire\Livewire;

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

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_active' => true,
            'is_direct_debit' => true,
            'requires_manual_transfer' => false,
        ]);

    $this->order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(MoneyTransfer::class)
        ->assertOk();
});

test('lists an overpaid sales invoice regardless of its payment type', function (): void {
    Order::query()
        ->whereKey($this->order->getKey())
        ->update([
            'invoice_number' => 'RE-OVERPAID',
            'total_gross_price' => 404.12,
            'balance' => -404.12,
            'payment_state' => Overpaid::$name,
        ]);

    $data = Livewire::test(MoneyTransfer::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id')->all())->toBe([$this->order->getKey()]);
});

test('does not list a partially paid sales invoice with an open balance', function (): void {
    Order::query()
        ->whereKey($this->order->getKey())
        ->update([
            'invoice_number' => 'RE-PARTIAL',
            'total_gross_price' => 404.12,
            'balance' => 100,
            'payment_state' => PartialPaid::$name,
        ]);

    $data = Livewire::test(MoneyTransfer::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    expect(data_get($data, 'data'))->toBeEmpty();
});

test('an overpaid invoice moves into the payment run when the run is created', function (): void {
    Order::query()
        ->whereKey($this->order->getKey())
        ->update([
            'invoice_number' => 'RE-OVERPAID',
            'total_gross_price' => 404.12,
            'balance' => -404.12,
            'payment_state' => Overpaid::$name,
        ]);

    CreatePaymentRun::make([
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        'orders' => [
            ['order_id' => $this->order->getKey(), 'amount' => -404.12],
        ],
    ])
        ->validate()
        ->execute();

    expect($this->order->refresh()->payment_state)->toBeInstanceOf(InOpenPaymentRun::class);
});
