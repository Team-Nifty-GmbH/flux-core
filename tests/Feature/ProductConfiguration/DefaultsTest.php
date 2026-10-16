<?php

use FluxErp\Actions\Product\SyncProductConfigurationDefaults;
use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Models\ProductConfigurationRule;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->product = configurationProduct(['is_configurable' => true]);
    $this->storage = configurationGroup(['name' => 'Storage']);
    $this->ssd = configurationItem($this->storage, configurationProduct(['name' => 'SSD']), ['max_amount' => 2]);
    attachConfigurationGroup($this->product, $this->storage, isRequired: true);
});

test('sync replaces the defaults', function (): void {
    $hdd = configurationItem($this->storage);

    SyncProductConfigurationDefaults::make([
        'product_id' => $this->product->getKey(),
        'defaults' => [['product_configuration_group_item_id' => $hdd->getKey(), 'amount' => 1]],
    ])->validate()->execute();

    $result = SyncProductConfigurationDefaults::make([
        'product_id' => $this->product->getKey(),
        'defaults' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]],
    ])->validate()->execute();

    expect($result)->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]]);
});

test('defaults are independent per product', function (): void {
    $hdd = configurationItem($this->storage);
    $other = configurationProduct(['is_configurable' => true]);
    attachConfigurationGroup($other, $this->storage);

    SyncProductConfigurationDefaults::make([
        'product_id' => $this->product->getKey(),
        'defaults' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]],
    ])->validate()->execute();
    SyncProductConfigurationDefaults::make([
        'product_id' => $other->getKey(),
        'defaults' => [['product_configuration_group_item_id' => $hdd->getKey(), 'amount' => 1]],
    ])->validate()->execute();

    expect($this->product->defaultConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]])
        ->and($other->defaultConfiguration())
        ->toBe([['product_configuration_group_item_id' => $hdd->getKey(), 'amount' => 1]]);
});

test('empty defaults are allowed even with a required group', function (): void {
    expect(SyncProductConfigurationDefaults::make([
        'product_id' => $this->product->getKey(),
        'defaults' => [],
    ])->validate()->execute())->toBe([]);
});

test('defaults must themselves be valid', function (): void {
    $foreign = configurationItem(configurationGroup());
    ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->ssd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Requires,
        'target_group_id' => configurationGroup()->getKey(),
    ]);

    SyncProductConfigurationDefaults::assertValidationErrors([
        'product_id' => $this->product->getKey(),
        'defaults' => [['product_configuration_group_item_id' => $foreign->getKey(), 'amount' => 1]],
    ], 'configuration');

    SyncProductConfigurationDefaults::assertValidationErrors([
        'product_id' => $this->product->getKey(),
        'defaults' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]],
    ], 'configuration');

    SyncProductConfigurationDefaults::assertValidationErrors([
        'product_id' => configurationProduct()->getKey(),
        'defaults' => [],
    ], 'product_id');
});
