<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

function configurationProduct(array $attributes = []): Product
{
    $product = Product::factory()->create(array_merge([
        'is_bundle' => false,
        'is_configurable' => false,
        'vat_rate_id' => VatRate::default()->getKey(),
    ], $attributes));
    $product->tenants()->attach(Tenant::default()->getKey());

    return $product;
}

function configurationGroup(array $attributes = []): ProductConfigurationGroup
{
    return ProductConfigurationGroup::factory()->create($attributes);
}

function configurationItem(
    ProductConfigurationGroup $group,
    ?Product $product = null,
    array $attributes = []
): ProductConfigurationGroupItem {
    return ProductConfigurationGroupItem::factory()->create(array_merge([
        'product_configuration_group_id' => $group->getKey(),
        'product_id' => ($product ?? configurationProduct())->getKey(),
    ], $attributes));
}

function attachConfigurationGroup(
    Product $product,
    ProductConfigurationGroup $group,
    bool $isRequired = false,
    int $sort = 0
): void {
    $product->productConfigurationGroups()->attach($group->getKey(), [
        'is_required' => $isRequired,
        'sort' => $sort,
    ]);
}

function configurationPrice(Product $product, PriceList $priceList, string $price): Price
{
    return Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $priceList->getKey(),
        'price' => $price,
    ]);
}

function configurationOrder(PriceList $priceList, OrderTypeEnum $orderTypeEnum = OrderTypeEnum::Order): Order
{
    Warehouse::default() ?? Warehouse::factory()->create(['is_default' => true]);
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    return Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => $orderTypeEnum,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => $priceList->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'vat_rate_id' => null,
        'is_locked' => false,
    ]);
}
