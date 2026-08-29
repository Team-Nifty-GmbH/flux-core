<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertOk();
});

test('delete removes the selected unlocked orders', function (): void {
    config(['queue.default' => 'sync']);
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
        'is_locked' => false,
    ]);

    Livewire::test(OrderList::class)
        ->set('selected', [$order->getKey()])
        ->call('delete')
        ->assertOk()
        ->assertSet('selected', []);

    $this->assertSoftDeleted($order);
});
