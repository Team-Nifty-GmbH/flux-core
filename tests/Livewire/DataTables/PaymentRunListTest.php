<?php

use FluxErp\Livewire\DataTables\PaymentRunList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentRunList::class)
        ->assertOk();
});

test('edit loads orders into payment run form', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
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
    ]);
    $paymentRun->orders()->attach($order->getKey(), ['amount' => '100.00']);

    $component = Livewire::test(PaymentRunList::class)
        ->call('edit', $paymentRun);

    $orders = $component->get('paymentRunForm.orders');

    expect($orders)->toBeArray()
        ->and($orders)->not->toBeEmpty()
        ->and($orders[0]['id'])->toBe($order->getKey());
});
