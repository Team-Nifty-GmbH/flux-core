<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Widgets\RevenueByPaymentType;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->contact = $contact;
    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);
});

function revenueOrder(mixed $test, PaymentType $paymentType, array $attributes = []): Order
{
    return Order::factory()->create(array_merge([
        'address_invoice_id' => $test->address->getKey(),
        'contact_id' => $test->contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $test->defaultLanguage->getKey(),
        'order_type_id' => $test->orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $test->dbTenant->getKey(),
        'invoice_date' => now()->toDateString(),
        'invoice_number' => 'INV-' . fake()->unique()->numberBetween(1000, 9999),
        'total_net_price' => 250,
    ], $attributes));
}

test('renders successfully', function (): void {
    Livewire::test(RevenueByPaymentType::class)
        ->assertOk();
});

test('an invoice lands in the series of its payment type', function (): void {
    $vorkasse = PaymentType::factory()->create(['name' => 'Vorkasse', 'is_active' => true]);
    $rechnung = PaymentType::factory()->create(['name' => 'Rechnung', 'is_active' => true]);

    revenueOrder($this, $vorkasse);

    $series = collect(Livewire::test(RevenueByPaymentType::class)->get('series'))->keyBy('name');

    expect(array_sum($series->get('Vorkasse')['data']))->toBe(250.0)
        ->and(array_sum($series->get('Rechnung')['data']))->toBe(0.0);
});

test('an order with a date but no invoice number is not counted as revenue', function (): void {
    $vorkasse = PaymentType::factory()->create(['name' => 'Vorkasse', 'is_active' => true]);

    // A draft or an offer carries the date but no number yet. whereBetween alone would
    // not catch this, the date is set.
    revenueOrder($this, $vorkasse, ['invoice_number' => null]);

    $series = collect(Livewire::test(RevenueByPaymentType::class)->get('series'))->keyBy('name');

    expect(array_sum($series->get('Vorkasse')['data']))->toBe(0.0);
});

test('a purchase order is not counted as revenue', function (): void {
    $vorkasse = PaymentType::factory()->create(['name' => 'Vorkasse', 'is_active' => true]);
    $purchase = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    revenueOrder($this, $vorkasse, ['order_type_id' => $purchase->getKey()]);

    $series = collect(Livewire::test(RevenueByPaymentType::class)->get('series'))->keyBy('name');

    expect(array_sum($series->get('Vorkasse')['data']))->toBe(0.0);
});
