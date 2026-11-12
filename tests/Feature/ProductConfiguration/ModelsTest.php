<?php

use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Models\PriceList;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\VatRate;

require_once __DIR__ . '/Helpers.php';

test('enums carry the spec values', function (): void {
    expect(ConfigurationSelectionTypeEnum::values())->toBe(['single', 'multiple'])
        ->and(ConfigurationPriceModeEnum::values())->toBe(['surcharge', 'product_price']);
});

test('every new model is in the morph map', function (): void {
    expect(morph_alias(ProductConfigurationGroup::class))->toBe('product_configuration_group')
        ->and(morph_alias(ProductConfigurationGroupItem::class))->toBe('product_configuration_group_item')
        ->and(morph_alias(ProductProductConfigurationGroup::class))->toBe('product_product_configuration_group');
});

test('a product reaches its groups and items', function (): void {
    $product = configurationProduct(['is_configurable' => true]);
    $storage = configurationGroup(['name' => 'Storage']);
    $power = configurationGroup(['name' => 'Power']);
    $ssd = configurationItem($storage, attributes: ['sort' => 2]);
    $hdd = configurationItem($storage, attributes: ['sort' => 1]);
    attachConfigurationGroup($product, $storage, isRequired: true, sort: 1);
    attachConfigurationGroup($product, $power, sort: 0);

    $groups = $product->productConfigurationGroups()->get();

    expect($groups->pluck('name')->all())->toBe(['Power', 'Storage'])
        ->and((bool) $groups->last()->pivot->is_required)->toBeTrue()
        ->and($storage->productConfigurationGroupItems()->pluck('id')->all())
        ->toBe([$hdd->getKey(), $ssd->getKey()]);
});

test('an order position reports its current configuration and its configured parent', function (): void {
    $priceList = PriceList::factory()->create(['is_net' => true]);
    $order = configurationOrder($priceList);
    $product = configurationProduct(['is_configurable' => true]);
    $item = configurationItem(configurationGroup());

    $parent = OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'product_id' => $product->getKey(),
        'tenant_id' => $order->tenant_id,
        'vat_rate_id' => VatRate::default()->getKey(),
        'is_free_text' => false,
    ]);
    OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'parent_id' => $parent->getKey(),
        'product_id' => $item->product_id,
        'product_configuration_group_item_id' => $item->getKey(),
        'amount_bundle' => 3,
        'tenant_id' => $order->tenant_id,
        'vat_rate_id' => VatRate::default()->getKey(),
        'is_free_text' => false,
    ]);

    expect($parent->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $item->getKey(), 'amount' => 3]])
        ->and(OrderPosition::hasConfiguredParent($parent->getKey()))->toBeTrue()
        ->and(OrderPosition::hasConfiguredParent(null))->toBeFalse();
});
