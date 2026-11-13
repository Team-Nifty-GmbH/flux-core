<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\OrderListByOrderType;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    Livewire::test(OrderListByOrderType::class, ['orderType' => $orderType->id])
        ->assertOk();
});

test('create dialog of a purchase list saves without delivery address', function (): void {
    $supplier = Contact::factory()->create(['has_delivery_lock' => false, 'credit_line' => null]);
    $invoiceAddress = Address::factory()->create([
        'contact_id' => $supplier->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $supplier->update([
        'delivery_address_id' => Address::factory()->create(['contact_id' => $supplier->getKey()])->getKey(),
        'invoice_address_id' => $invoiceAddress->getKey(),
        'main_address_id' => $invoiceAddress->getKey(),
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);

    Livewire::test(OrderListByOrderType::class, ['orderType' => $orderType->getKey()])
        ->set('order.contact_id', $supplier->getKey())
        ->call('fetchContactData')
        ->assertSet('order.address_delivery_id', null)
        ->set('order.payment_type_id', PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey())
        ->set('order.price_list_id', PriceList::factory()->create()->getKey())
        ->set('order.language_id', $this->defaultLanguage->getKey())
        ->call('save')
        ->assertHasNoErrors();

    $order = Order::query()->where('order_type_id', $orderType->getKey())->first();

    expect($order)->not->toBeNull()
        ->and($order->address_delivery_id)->toBeNull();
});
