<?php

use FluxErp\Actions\ProductConfigurationGroup\CreateProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\DeleteProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\UpdateProductConfigurationGroup;
use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;

require_once __DIR__ . '/Helpers.php';

test('create a multiple choice group', function (): void {
    $group = CreateProductConfigurationGroup::make([
        'name' => 'Accessories',
        'description' => 'Pick what you need',
        'selection_type_enum' => 'multiple',
        'price_mode_enum' => 'product_price',
        'min_selections' => 1,
        'max_selections' => 3,
    ])->validate()->execute();

    expect($group->name)->toBe('Accessories')
        ->and($group->selection_type_enum)->toBe(ConfigurationSelectionTypeEnum::Multiple)
        ->and($group->price_mode_enum)->toBe(ConfigurationPriceModeEnum::ProductPrice)
        ->and($group->min_selections)->toBe(1)
        ->and($group->max_selections)->toBe(3);
});

test('create defaults to single choice with surcharge and drops min and max', function (): void {
    $group = CreateProductConfigurationGroup::make([
        'name' => 'Storage',
        'min_selections' => 1,
        'max_selections' => 2,
    ])->validate()->execute();

    expect($group->selection_type_enum)->toBe(ConfigurationSelectionTypeEnum::Single)
        ->and($group->price_mode_enum)->toBe(ConfigurationPriceModeEnum::Surcharge)
        ->and($group->min_selections)->toBeNull()
        ->and($group->max_selections)->toBeNull();
});

test('create requires a name and a valid enum', function (): void {
    CreateProductConfigurationGroup::assertValidationErrors(
        ['selection_type_enum' => 'several'],
        ['name', 'selection_type_enum']
    );
});

test('create rejects a minimum above the maximum', function (): void {
    CreateProductConfigurationGroup::assertValidationErrors([
        'name' => 'Accessories',
        'selection_type_enum' => 'multiple',
        'min_selections' => 3,
        'max_selections' => 2,
    ], 'min_selections');
});

test('update checks min against the stored max', function (): void {
    $group = configurationGroup([
        'selection_type_enum' => ConfigurationSelectionTypeEnum::Multiple,
        'max_selections' => 2,
    ]);

    UpdateProductConfigurationGroup::assertValidationErrors(
        ['id' => $group->getKey(), 'min_selections' => 5],
        'min_selections'
    );
});

test('switching a group to single clears min and max', function (): void {
    $group = configurationGroup([
        'selection_type_enum' => ConfigurationSelectionTypeEnum::Multiple,
        'min_selections' => 1,
        'max_selections' => 2,
    ]);

    $updated = UpdateProductConfigurationGroup::make([
        'id' => $group->getKey(),
        'selection_type_enum' => 'single',
    ])->validate()->execute();

    expect($updated->min_selections)->toBeNull()
        ->and($updated->max_selections)->toBeNull();
});

test('delete soft deletes an unused group', function (): void {
    $group = configurationGroup();

    $result = DeleteProductConfigurationGroup::make(['id' => $group->getKey()])->validate()->execute();

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('product_configuration_groups', ['id' => $group->getKey()]);
});

test('delete refuses a group attached to a product', function (): void {
    $group = configurationGroup();
    attachConfigurationGroup(configurationProduct(['is_configurable' => true]), $group);

    DeleteProductConfigurationGroup::assertValidationErrors(['id' => $group->getKey()], 'id');
});
