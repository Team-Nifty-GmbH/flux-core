<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Accounting\TransactionAssignments;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Models\Transaction;
use FluxErp\Models\VatRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();

    $this->currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $this->language = Language::factory()->create([
        'is_default' => true,
    ]);

    $this->priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'is_default' => true,
        ]);

    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create();

    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->vatRate = VatRate::factory()->create([
        'rate_percentage' => 0.19,
    ]);

    $this->purchaseSubscriptionOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::PurchaseSubscription,
            'is_active' => true,
        ]);

    $this->targetOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Purchase,
            'is_active' => true,
        ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $this->purchaseSubscriptionOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'parent_id' => null,
        'order_number' => 'K-100',
        'system_delivery_date' => '2026-07-01',
        'system_delivery_date_end' => null,
        'is_locked' => false,
        'shipping_costs_net_price' => 0,
    ]);

    CreateOrderPosition::make([
        'order_id' => $this->order->getKey(),
        'name' => 'Subscription Fee',
        'vat_rate_id' => $this->vatRate->getKey(),
        'amount' => 1,
        'unit_price' => 1500.00,
        'is_net' => false,
    ])
        ->validate()
        ->execute();
});

test('adjust order to payment updates the order total', function (): void {
    (new ProcessSubscriptionOrder())($this->order->getKey(), $this->targetOrderType->getKey());

    $child = $this->order->createdOrders()->latest('id')->first();

    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'amount' => -1512.38,
    ]);

    $assignment = OrderTransaction::query()->create([
        'transaction_id' => $transaction->getKey(),
        'order_id' => $child->getKey(),
        'amount' => -1512.38,
        'is_accepted' => false,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('editOrderTransaction', $assignment->getAttribute('pivot_id'))
        ->set('orderTransactionForm.amount', '1512.38')
        ->call('adjustOrderToPayment')
        ->assertHasNoErrors();

    expect(bcround($child->refresh()->total_gross_price, 2))->toBe('1512.38')
        ->and($assignment->refresh()->is_accepted)->toBeTrue();
});

test('adjust order to payment with null amount does nothing', function (): void {
    (new ProcessSubscriptionOrder())($this->order->getKey(), $this->targetOrderType->getKey());

    $child = $this->order->createdOrders()->latest('id')->first();
    $originalTotal = $child->total_gross_price;

    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'amount' => -1512.38,
    ]);

    $assignment = OrderTransaction::query()->create([
        'transaction_id' => $transaction->getKey(),
        'order_id' => $child->getKey(),
        'amount' => -1512.38,
        'is_accepted' => false,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('editOrderTransaction', $assignment->getAttribute('pivot_id'))
        ->set('orderTransactionForm.amount', null)
        ->call('adjustOrderToPayment')
        ->assertHasNoErrors();

    expect($child->refresh()->total_gross_price)->toBe($originalTotal)
        ->and($assignment->refresh()->is_accepted)->toBeFalse();
});
