<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\SignatureLinkGenerator;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use Livewire\Livewire;

beforeEach(function (): void {
    $tenant = Tenant::factory()->create([
        'is_default' => true,
    ]);
    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);
    $contact = Contact::factory()->create([
        'tenant_id' => $tenant->id,
    ]);
    $priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $tenant, relationship: 'tenants')
        ->create([
            'is_default' => true,
        ]);

    $orderType = OrderType::factory()->create([
        'tenant_id' => $tenant->id,
        'order_type_enum' => OrderTypeEnum::Order->value,
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $tenant->id,
        'contact_id' => $contact->id,
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $tenant->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'price_list_id' => $priceList->id,
        'payment_type_id' => $paymentType->id,
        'order_type_id' => $orderType->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(SignatureLinkGenerator::class, ['modelType' => Order::class, 'modelId' => $this->order->id])
        ->assertOk();
});
