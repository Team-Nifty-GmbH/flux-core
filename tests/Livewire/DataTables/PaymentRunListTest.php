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
        ->call('edit', $paymentRun)
        ->assertOpensModal('execute-payment-run');

    $orders = $component->get('paymentRunForm.orders');

    expect($orders)->toBeArray()
        ->and($orders)->not->toBeEmpty()
        ->and($orders[0]['id'])->toBe($order->getKey());
});

test('edit is renderless so it does not trigger empty re-render', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    // DataTable.dehydrate() clears $this->data after each render.
    // If edit() triggers a re-render, render() sees initialized=true,
    // skips loadData(), and renders with empty data → table goes blank.
    // edit() must be #[Renderless] to avoid this.
    $component = Livewire::test(PaymentRunList::class)
        ->call('edit', $paymentRun);

    $method = new ReflectionMethod(PaymentRunList::class, 'edit');
    $attributes = $method->getAttributes(\Livewire\Attributes\Renderless::class);

    expect($attributes)->not->toBeEmpty('edit() must have #[Renderless] to prevent clearing table data');
});

test('the list carries the total amount of a payment run', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();

    $orderData = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ];

    $paymentRun->orders()->attach(Order::factory()->create($orderData)->getKey(), ['amount' => '100.00']);
    $paymentRun->orders()->attach(Order::factory()->create($orderData)->getKey(), ['amount' => '250.50']);

    expect((float) $paymentRun->refresh()->total_amount)->toBe(350.50);
});

test('removing an order updates the stored total', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();

    $orderData = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ];

    $stays = Order::factory()->create($orderData);
    $goes = Order::factory()->create($orderData);

    $paymentRun->orders()->attach($stays->getKey(), ['amount' => '100.00']);
    $paymentRun->orders()->attach($goes->getKey(), ['amount' => '250.50']);

    $paymentRun->orders()->detach($goes->getKey());

    expect((float) $paymentRun->refresh()->total_amount)->toBe(100.0);
});

test('the total amount is enabled by default', function (): void {
    expect((new PaymentRunList())->enabledCols)->toContain('total_amount');
});
