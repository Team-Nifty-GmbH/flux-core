<?php

use FluxErp\Actions\ProductConfigurationGroupItem\CreateProductConfigurationGroupItem;
use FluxErp\Actions\ProductConfigurationGroupItem\DeleteProductConfigurationGroupItem;
use FluxErp\Actions\ProductConfigurationGroupItem\UpdateProductConfigurationGroupItem;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->group = configurationGroup();
});

test('create an option with a negative surcharge', function (): void {
    $product = configurationProduct();

    $item = CreateProductConfigurationGroupItem::make([
        'product_configuration_group_id' => $this->group->getKey(),
        'product_id' => $product->getKey(),
        'surcharge' => -15.5,
        'max_amount' => 4,
    ])->validate()->execute();

    expect(bccomp($item->surcharge, '-15.5', 2))->toBe(0)
        ->and($item->max_amount)->toBe(4)
        ->and($item->sort)->toBe(0);
});

test('create rejects configurable, bundle and variant parent products', function (array $attributes): void {
    $product = configurationProduct($attributes);

    CreateProductConfigurationGroupItem::assertValidationErrors([
        'product_configuration_group_id' => $this->group->getKey(),
        'product_id' => $product->getKey(),
    ], 'product_id');
})->with([
    'configurable' => [['is_configurable' => true]],
    'bundle' => [['is_bundle' => true]],
    'variant parent' => [['is_variant_parent' => true]],
]);

test('create rejects the same product twice in a group', function (): void {
    $item = configurationItem($this->group);

    CreateProductConfigurationGroupItem::assertValidationErrors([
        'product_configuration_group_id' => $this->group->getKey(),
        'product_id' => $item->product_id,
    ], 'product_id');
});

test('create rejects a max amount below one', function (): void {
    CreateProductConfigurationGroupItem::assertValidationErrors([
        'product_configuration_group_id' => $this->group->getKey(),
        'product_id' => configurationProduct()->getKey(),
        'max_amount' => 0,
    ], 'max_amount');
});

test('update changes surcharge and sort', function (): void {
    $item = configurationItem($this->group);

    $updated = UpdateProductConfigurationGroupItem::make([
        'id' => $item->getKey(),
        'surcharge' => 20,
        'sort' => 3,
    ])->validate()->execute();

    expect(bccomp($updated->surcharge, '20', 2))->toBe(0)
        ->and($updated->sort)->toBe(3);
});

test('update rejects a product already in the group', function (): void {
    $first = configurationItem($this->group);
    $second = configurationItem($this->group);

    UpdateProductConfigurationGroupItem::assertValidationErrors([
        'id' => $second->getKey(),
        'product_id' => $first->product_id,
    ], 'product_id');
});

test('update rejects configurable, bundle and variant parent products', function (array $attributes): void {
    $item = configurationItem($this->group);
    $product = configurationProduct($attributes);

    UpdateProductConfigurationGroupItem::assertValidationErrors([
        'id' => $item->getKey(),
        'product_id' => $product->getKey(),
    ], 'product_id');
})->with([
    'configurable' => [['is_configurable' => true]],
    'bundle' => [['is_bundle' => true]],
    'variant parent' => [['is_variant_parent' => true]],
]);

test('update accepts a plain product', function (): void {
    $item = configurationItem($this->group);
    $product = configurationProduct();

    $updated = UpdateProductConfigurationGroupItem::make([
        'id' => $item->getKey(),
        'product_id' => $product->getKey(),
    ])->validate()->execute();

    expect($updated->product_id)->toBe($product->getKey());
});

test('delete removes the option', function (): void {
    $item = configurationItem($this->group);

    expect(DeleteProductConfigurationGroupItem::make(['id' => $item->getKey()])->validate()->execute())
        ->toBeTrue();
    $this->assertDatabaseMissing('product_configuration_group_items', ['id' => $item->getKey()]);
});
