<?php

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
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TransactionAssignments::class)
        ->assertOk();
});

test('assign orders from selectedOrders property', function (): void {
    $bankConnection = BankConnection::factory()->create();
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
        'balance' => 100,
    ]);

    Livewire::test(TransactionAssignments::class)
        ->call('assignOrdersModal', $transaction)
        ->set('selectedOrders', [$order->getKey()])
        ->call('assignOrders', [$order->getKey()])
        ->assertOk();

    expect(OrderTransaction::query()
        ->where('transaction_id', $transaction->getKey())
        ->where('order_id', $order->getKey())
        ->exists()
    )->toBeTrue();
});
