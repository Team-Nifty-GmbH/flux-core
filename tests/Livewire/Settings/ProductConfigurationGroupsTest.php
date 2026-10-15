<?php

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Livewire\Settings\ProductConfigurationGroups;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationRule;
use Livewire\Livewire;

require_once __DIR__ . '/../../Feature/ProductConfiguration/Helpers.php';

test('renders successfully', function (): void {
    Livewire::test(ProductConfigurationGroups::class)->assertOk();
});

test('edit without a group opens an empty form', function (): void {
    Livewire::test(ProductConfigurationGroups::class)
        ->call('edit')
        ->assertSet('productConfigurationGroupForm.id', null)
        ->assertSet('productConfigurationGroupForm.selection_type_enum', 'single')
        ->assertSet('items', [])
        ->assertOpensModal('edit-product-configuration-group-modal');
});

test('can create a group', function (): void {
    Livewire::test(ProductConfigurationGroups::class)
        ->call('edit')
        ->set('productConfigurationGroupForm.name', 'Storage')
        ->set('productConfigurationGroupForm.price_mode_enum', 'product_price')
        ->call('save')
        ->assertReturned(true)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('product_configuration_groups', [
        'name' => 'Storage',
        'price_mode_enum' => 'product_price',
    ]);
});

test('can add an option and a rule to a group', function (): void {
    $group = configurationGroup(['name' => 'Graphics']);
    $power = configurationGroup(['name' => 'Power']);
    $product = configurationProduct(['name' => 'GPU']);

    $component = Livewire::test(ProductConfigurationGroups::class)
        ->call('edit', $group->getKey())
        ->call('editItem')
        ->set('productConfigurationGroupItemForm.product_id', $product->getKey())
        ->set('productConfigurationGroupItemForm.surcharge', 50)
        ->call('saveItem')
        ->assertReturned(true);

    $itemId = $group->productConfigurationGroupItems()->value('id');

    $component->assertSet('items.0.product_name', 'GPU')
        ->call('editRule', $itemId)
        ->assertSet('ruleOptions.groups', [['id' => $power->getKey(), 'name' => 'Power']])
        ->set('productConfigurationRuleForm.rule_type_enum', 'requires')
        ->set('productConfigurationRuleForm.target_group_id', $power->getKey())
        ->call('saveRule')
        ->assertReturned(true)
        ->assertSet('items.0.rules.0.target', 'Power');

    expect(ProductConfigurationRule::query()->sole())
        ->rule_type_enum->toBe(ConfigurationRuleTypeEnum::Requires)
        ->target_group_id->toBe($power->getKey());
});

test('delete refuses a group attached to a product and keeps it', function (): void {
    $group = configurationGroup();
    attachConfigurationGroup(configurationProduct(['is_configurable' => true]), $group);

    Livewire::test(ProductConfigurationGroups::class)
        ->call('delete', $group->getKey())
        ->assertReturned(false);

    expect(ProductConfigurationGroup::query()->whereKey($group->getKey())->exists())->toBeTrue();
});
