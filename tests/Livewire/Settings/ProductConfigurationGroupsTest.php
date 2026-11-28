<?php

use FluxErp\Livewire\Settings\ProductConfigurationGroups;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
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

test('can add an option to a group', function (): void {
    $group = configurationGroup(['name' => 'Graphics']);
    $product = configurationProduct(['name' => 'GPU']);

    $component = Livewire::test(ProductConfigurationGroups::class)
        ->call('edit', $group->getKey())
        ->call('editItem')
        ->set('productConfigurationGroupItemForm.product_id', $product->getKey())
        ->set('productConfigurationGroupItemForm.surcharge', 50)
        ->call('saveItem')
        ->assertReturned(true);

    $component->assertSet('items.0.product_name', 'GPU');

    expect($group->productConfigurationGroupItems()->sole())
        ->product_id->toBe($product->getKey())
        ->and(bccomp($group->productConfigurationGroupItems()->value('surcharge'), '50', 2))->toBe(0);
});

test('delete refuses a group attached to a product and keeps it', function (): void {
    $group = configurationGroup();
    attachConfigurationGroup(configurationProduct(['is_configurable' => true]), $group);

    Livewire::test(ProductConfigurationGroups::class)
        ->call('delete', $group->getKey())
        ->assertReturned(false);

    expect(ProductConfigurationGroup::query()->whereKey($group->getKey())->exists())->toBeTrue();
});

test('deleteItem removes the option', function (): void {
    $group = configurationGroup();
    $item = configurationItem($group);

    Livewire::test(ProductConfigurationGroups::class)
        ->call('edit', $group->getKey())
        ->assertCount('items', 1)
        ->call('deleteItem', $item->getKey())
        ->assertReturned(true)
        ->assertSet('items', []);

    expect(ProductConfigurationGroupItem::query()->whereKey($item->getKey())->exists())->toBeFalse();
});
