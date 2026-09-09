<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\OrderPosition\Activities;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use Livewire\Livewire;

beforeEach(function (): void {
    $tenant = Tenant::factory()->create([
        'is_default' => true,
    ]);
    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);
    $contact = Contact::factory()
        ->hasAttached(factory: $tenant, relationship: 'tenants')
        ->create();
    $priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $tenant, relationship: 'tenants')
        ->create([
            'is_default' => true,
        ]);

    $orderType = OrderType::factory()
        ->hasAttached(factory: $tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Order->value,
        ]);

    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'is_delivery_address' => true,
        'is_invoice_address' => true,
        'is_main_address' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $tenant->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'price_list_id' => $priceList->id,
        'payment_type_id' => $paymentType->id,
        'order_type_id' => $orderType->id,
    ]);

    $this->orderPosition = OrderPosition::factory()->create([
        'tenant_id' => $tenant->id,
        'order_id' => $order->id,
        'vat_rate_id' => VatRate::factory()->create()->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Activities::class)
        ->assertOk();
});

test('loads activities on event', function (): void {
    Livewire::test(Activities::class)
        ->assertSet('modelId', null)
        ->dispatch('load-order-position-activities', orderPositionId: $this->orderPosition->id)
        ->assertSet('modelId', $this->orderPosition->id)
        ->assertOk()
        ->assertCount('activities', 1);
});
