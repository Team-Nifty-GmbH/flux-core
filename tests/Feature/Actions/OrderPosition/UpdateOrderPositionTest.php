<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

it('uses order vat rate instead of product vat rate when changing product', function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $domesticVatRate = VatRate::factory()->create([
        'rate_percentage' => 0.19,
        'name' => 'Voll',
    ]);

    $euVatRate = VatRate::factory()->create([
        'rate_percentage' => 0,
        'name' => 'EU-Land (Ohne MwSt)',
    ]);

    $priceList = PriceList::factory()->create(['is_net' => true]);
    $currency = Currency::default();
    $paymentType = PaymentType::default();

    $orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $productA = Product::factory()->create([
        'vat_rate_id' => $domesticVatRate->getKey(),
    ]);

    Price::factory()->create([
        'product_id' => $productA->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 1000,
    ]);

    $productB = Product::factory()->create([
        'vat_rate_id' => $domesticVatRate->getKey(),
    ]);

    Price::factory()->create([
        'product_id' => $productB->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 2000,
    ]);

    // Order has EU vat rate (e.g. EU customer)
    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'vat_rate_id' => $euVatRate->getKey(),
        'is_locked' => false,
    ]);

    // Position created with EU vat rate (from order)
    $position = OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => $order->getKey(),
        'product_id' => $productA->getKey(),
        'vat_rate_id' => $euVatRate->getKey(),
        'amount' => 1,
        'unit_net_price' => 1000,
        'unit_gross_price' => 1000,
        'total_net_price' => 1000,
        'total_gross_price' => 1000,
    ]);

    // Change product without explicitly setting vat_rate_id
    $result = UpdateOrderPosition::make([
        'id' => $position->getKey(),
        'product_id' => $productB->getKey(),
    ])->validate()->execute();

    // Should keep order's EU vat rate, NOT use productB's domestic vat rate
    expect($result->vat_rate_id)->toBe($euVatRate->getKey());
});

it('allows explicit vat rate override on create even when order has vat rate', function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $domesticVatRate = VatRate::factory()->create([
        'rate_percentage' => 0.19,
        'name' => 'Voll',
    ]);

    $euVatRate = VatRate::factory()->create([
        'rate_percentage' => 0,
        'name' => 'EU-Land (Ohne MwSt)',
    ]);

    $priceList = PriceList::factory()->create(['is_net' => true]);
    $currency = Currency::default();
    $paymentType = PaymentType::default();

    $orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $product = Product::factory()->create([
        'vat_rate_id' => $domesticVatRate->getKey(),
    ]);

    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => 1000,
    ]);

    // Order has EU vat rate
    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => $priceList->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'currency_id' => $currency->getKey(),
        'vat_rate_id' => $euVatRate->getKey(),
        'is_locked' => false,
    ]);

    // Explicitly create position with domestic vat rate (manual override)
    $position = CreateOrderPosition::make([
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'vat_rate_id' => $domesticVatRate->getKey(),
        'amount' => 1,
    ])->validate()->execute();

    // Should respect the explicitly provided vat rate
    expect($position->vat_rate_id)->toBe($domesticVatRate->getKey());
});
