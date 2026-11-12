<?php

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct;
use FluxErp\Actions\Product\ProductProductConfigurationGroup\CreateProductProductConfigurationGroup;
use FluxErp\Actions\Product\ProductProductConfigurationGroup\DeleteProductProductConfigurationGroup;
use FluxErp\Actions\Product\ProductProductConfigurationGroup\UpdateProductProductConfigurationGroup;
use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationDefault;
use FluxErp\Models\VatRate;

require_once __DIR__ . '/Helpers.php';

test('a new product can be configurable', function (): void {
    $product = CreateProduct::make(['name' => 'Server', 'is_configurable' => true])->validate()->execute();

    expect($product->is_configurable)->toBeTrue();
});

test('a configurable product cannot be a bundle on create', function (): void {
    CreateProduct::assertValidationErrors([
        'name' => 'Server',
        'is_configurable' => true,
        'is_bundle' => true,
        'bundle_type_enum' => 'standard',
        'bundle_products' => [['id' => configurationProduct()->getKey(), 'count' => 1]],
    ], 'is_configurable');
});

test('a configurable product cannot be a variant on create', function (): void {
    CreateProduct::assertValidationErrors([
        'name' => 'Server',
        'is_configurable' => true,
        'vat_rate_id' => VatRate::default()->getKey(),
        'parent_id' => configurationProduct()->getKey(),
    ], 'is_configurable');
});

test('a configurable product cannot get variants', function (): void {
    $parent = configurationProduct(['is_configurable' => true]);

    CreateProduct::assertValidationErrors([
        'name' => 'Variant',
        'vat_rate_id' => VatRate::default()->getKey(),
        'parent_id' => $parent->getKey(),
    ], 'parent_id');
});

test('an existing bundle or variant parent cannot become configurable', function (array $attributes): void {
    $product = configurationProduct($attributes);

    UpdateProduct::assertValidationErrors(
        ['id' => $product->getKey(), 'is_configurable' => true],
        'is_configurable'
    );
})->with([
    'bundle' => [['is_bundle' => true]],
    'variant parent' => [['is_variant_parent' => true]],
]);

test('a configurable product cannot become a bundle', function (): void {
    $product = configurationProduct(['is_configurable' => true]);

    CreateProductBundleProduct::assertValidationErrors([
        'product_id' => $product->getKey(),
        'bundle_product_id' => configurationProduct()->getKey(),
        'count' => 1,
    ], 'product_id');
});

test('assign a group to a configurable product', function (): void {
    $product = configurationProduct(['is_configurable' => true]);
    $group = configurationGroup();

    $pivot = CreateProductProductConfigurationGroup::make([
        'product_id' => $product->getKey(),
        'product_configuration_group_id' => $group->getKey(),
        'is_required' => true,
    ])->validate()->execute();

    expect($pivot)->toBeInstanceOf(ProductProductConfigurationGroup::class)
        ->and($pivot->is_required)->toBeTrue()
        ->and($pivot->sort)->toBe(0);
});

test('groups attach only to configurable products and only once', function (): void {
    $group = configurationGroup();
    $configurable = configurationProduct(['is_configurable' => true]);
    attachConfigurationGroup($configurable, $group);

    CreateProductProductConfigurationGroup::assertValidationErrors([
        'product_id' => configurationProduct()->getKey(),
        'product_configuration_group_id' => $group->getKey(),
    ], 'product_id');

    CreateProductProductConfigurationGroup::assertValidationErrors([
        'product_id' => $configurable->getKey(),
        'product_configuration_group_id' => $group->getKey(),
    ], 'product_configuration_group_id');
});

test('update the requirement and sort of an assignment', function (): void {
    $product = configurationProduct(['is_configurable' => true]);
    attachConfigurationGroup($product, configurationGroup());
    $pivotId = $product->productConfigurationGroups()->first()->pivot->pivot_id;

    $pivot = UpdateProductProductConfigurationGroup::make([
        'pivot_id' => $pivotId,
        'is_required' => true,
        'sort' => 4,
    ])->validate()->execute();

    expect($pivot->is_required)->toBeTrue()->and($pivot->sort)->toBe(4);
});

test('detaching a group removes the product defaults of that group only', function (): void {
    $product = configurationProduct(['is_configurable' => true]);
    $otherProduct = configurationProduct(['is_configurable' => true]);
    $storage = configurationGroup();
    $power = configurationGroup();
    $ssd = configurationItem($storage);
    $psu = configurationItem($power);
    attachConfigurationGroup($product, $storage);
    attachConfigurationGroup($product, $power);
    attachConfigurationGroup($otherProduct, $storage);

    foreach ([[$product, $ssd], [$product, $psu], [$otherProduct, $ssd]] as [$owner, $item]) {
        ProductConfigurationDefault::query()->create([
            'product_id' => $owner->getKey(),
            'product_configuration_group_item_id' => $item->getKey(),
            'amount' => 1,
        ]);
    }

    $pivotId = ProductProductConfigurationGroup::query()
        ->where('product_id', $product->getKey())
        ->where('product_configuration_group_id', $storage->getKey())
        ->value('pivot_id');

    DeleteProductProductConfigurationGroup::make(['pivot_id' => $pivotId])->validate()->execute();

    expect($product->defaultConfiguration())
        ->toBe([['product_configuration_group_item_id' => $psu->getKey(), 'amount' => 1]])
        ->and($otherProduct->defaultConfiguration())
        ->toBe([['product_configuration_group_item_id' => $ssd->getKey(), 'amount' => 1]]);
});

test('switching is_configurable off detaches groups and defaults', function (): void {
    $product = configurationProduct(['is_configurable' => true]);
    $group = configurationGroup();
    $item = configurationItem($group);
    attachConfigurationGroup($product, $group);
    ProductConfigurationDefault::query()->create([
        'product_id' => $product->getKey(),
        'product_configuration_group_item_id' => $item->getKey(),
        'amount' => 1,
    ]);

    UpdateProduct::make(['id' => $product->getKey(), 'is_configurable' => false])->validate()->execute();

    expect($product->productConfigurationGroups()->count())->toBe(0)
        ->and($product->defaultConfiguration())->toBe([]);
});
