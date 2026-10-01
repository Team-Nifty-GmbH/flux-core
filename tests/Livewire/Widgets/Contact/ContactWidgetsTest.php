<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\Contact\OpenItems;
use FluxErp\Livewire\Widgets\Contact\Orders;
use FluxErp\Livewire\Widgets\Contact\PaymentBehaviour;
use FluxErp\Livewire\Widgets\Contact\RevenueOverTime;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use Illuminate\Support\Number;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $this->otherContact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $orderTypes = [
        OrderTypeEnum::Order->value => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ]),
        OrderTypeEnum::Offer->value => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Offer,
            'is_active' => true,
        ]),
    ];

    $this->makeOrder = function (Contact $contact, array $attributes = [], OrderTypeEnum $orderTypeEnum = OrderTypeEnum::Order) use ($orderTypes): Order {
        $address = Address::factory()->create([
            'contact_id' => $contact->getKey(),
            'is_main_address' => true,
        ]);

        $order = Order::factory()->create([
            'address_invoice_id' => $address->getKey(),
            'contact_id' => $contact->getKey(),
            'currency_id' => Currency::default()->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'order_type_id' => $orderTypes[$orderTypeEnum->value]->getKey(),
            'payment_type_id' => PaymentType::default()->getKey(),
            'price_list_id' => PriceList::default()->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
        ]);

        Order::query()->whereKey($order->getKey())->update($attributes);

        return $order->refresh();
    };
});

test('revenue over time only counts invoices of the contact', function (): void {
    ($this->makeOrder)($this->contact, [
        'invoice_date' => now(),
        'invoice_number' => 'RE-1',
        'total_net_price' => 300,
    ]);
    ($this->makeOrder)($this->otherContact, [
        'invoice_date' => now(),
        'invoice_number' => 'RE-2',
        'total_net_price' => 999,
    ]);

    $series = Livewire::test(RevenueOverTime::class, ['contactId' => $this->contact->getKey()])
        ->call('calculateChart')
        ->get('series');

    expect(array_sum(data_get($series, '1.data')))->toEqual(300);
});

test('open items only count open invoices of the contact', function (): void {
    ($this->makeOrder)($this->contact, [
        'balance' => 300,
        'invoice_date' => now(),
        'invoice_number' => 'RE-1',
        'payment_state' => Open::$name,
    ]);
    ($this->makeOrder)($this->otherContact, [
        'balance' => 999,
        'invoice_date' => now(),
        'invoice_number' => 'RE-2',
        'payment_state' => Open::$name,
    ]);

    $sum = Livewire::test(OpenItems::class, ['contactId' => $this->contact->getKey()])
        ->call('calculateSum')
        ->get('sum');

    expect($sum)->toStartWith(Number::abbreviate(300, 2));
});

test('orders count the orders of the contact and their volume', function (): void {
    ($this->makeOrder)($this->contact, ['order_date' => now(), 'total_net_price' => 100]);
    ($this->makeOrder)($this->contact, ['order_date' => now(), 'total_net_price' => 250]);
    ($this->makeOrder)($this->contact, ['order_date' => now(), 'total_net_price' => 777], OrderTypeEnum::Offer);
    ($this->makeOrder)($this->otherContact, ['order_date' => now(), 'total_net_price' => 999]);

    $component = Livewire::test(Orders::class, ['contactId' => $this->contact->getKey()])
        ->set('timeFrame', TimeFrameEnum::ThisYear)
        ->call('calculateSum');

    expect($component->get('sum'))->toEqual(2)
        ->and($component->get('subValue'))->toContain(Number::format(350, 2));
});

test('payment behaviour shows the average days until payment and the overdue share', function (): void {
    $paid = ($this->makeOrder)($this->contact, [
        'balance' => 0,
        'invoice_date' => now()->startOfYear(),
        'invoice_number' => 'RE-1',
        'payment_state' => Paid::$name,
    ]);
    $transaction = Transaction::factory()->create([
        'amount' => 100,
        'contact_bank_connection_id' => null,
        'value_date' => now()->startOfYear()->addDays(12),
    ]);
    OrderTransaction::query()->insert([
        'order_id' => $paid->getKey(),
        'transaction_id' => $transaction->getKey(),
        'amount' => 100,
        'is_accepted' => true,
    ]);
    ($this->makeOrder)($this->contact, [
        'balance' => 50,
        'invoice_date' => now()->startOfYear(),
        'invoice_number' => 'RE-2',
        'payment_state' => Open::$name,
        'payment_target_date' => now()->subDay(),
    ]);
    ($this->makeOrder)($this->contact, [
        'balance' => 50,
        'invoice_date' => now()->startOfYear(),
        'invoice_number' => 'RE-3',
        'payment_state' => Open::$name,
        'payment_target_date' => now()->addWeek(),
    ]);

    $component = Livewire::test(PaymentBehaviour::class, ['contactId' => $this->contact->getKey()])
        ->set('timeFrame', TimeFrameEnum::ThisYear)
        ->call('calculateSum');

    expect($component->get('sum'))->toStartWith('12')
        ->and($component->get('subValue'))->toContain('50');
});
