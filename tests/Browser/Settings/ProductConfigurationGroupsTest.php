<?php

use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;

require_once __DIR__ . '/../../Feature/ProductConfiguration/Helpers.php';

function configurationGroupsComponent(string $js): string
{
    return sprintf(<<<'JS'
        () => {
            const component = Livewire.all().find((item) => item.name === 'settings.product-configuration-groups');
            const $wire = component.$wire;

            return %s;
        }
    JS, $js);
}

function groupModal(string $suffix = ''): string
{
    return '#edit-product-configuration-group-modal' . $suffix;
}

function itemModal(string $suffix = ''): string
{
    return '#edit-product-configuration-group-item-modal' . $suffix;
}

test('groups and options created in the teleported modals are shown again when reopened', function (): void {
    $product = configurationProduct(['name' => 'Browser Option Product']);

    $page = visit(route('settings.product-configuration-groups'))->assertNoSmoke();
    waitForDataTable($page);

    $page->click('New');
    waitForElement($page, groupModal());
    $page->fill(groupModal(' input[wire\\:model="productConfigurationGroupForm.name"]'), 'Browser Group');
    $page->click(groupModal(' button:has-text("Save")'));
    $page->wait(1);

    $group = ProductConfigurationGroup::query()->where('name', 'Browser Group')->sole();

    $page->script(configurationGroupsComponent('$wire.edit(' . $group->getKey() . ')'));
    $page->wait(1);
    $page->click(groupModal(' button:has-text("Add option")'));
    waitForElement($page, itemModal());
    $page->script(configurationGroupsComponent(
        '$wire.$set("productConfigurationGroupItemForm.product_id", ' . $product->getKey() . ', false)'
    ));
    $page->fill(itemModal(' input[wire\\:model="productConfigurationGroupItemForm.surcharge"]'), '12.5');
    $page->click(itemModal(' button:has-text("Save")'));
    $page->wait(1);

    $item = ProductConfigurationGroupItem::query()->where('product_configuration_group_id', $group->getKey())->sole();
    expect($item->product_id)->toBe($product->getKey());

    $page->click(groupModal(' button:has-text("Cancel")'));

    $page->click('New');
    waitForElement($page, groupModal());
    $page->fill(groupModal(' input[wire\\:model="productConfigurationGroupForm.name"]'), 'Second Group');
    $page->click(groupModal(' button:has-text("Save")'));
    $page->wait(1);

    $second = ProductConfigurationGroup::query()->where('name', 'Second Group')->sole();

    $page->script(configurationGroupsComponent('$wire.edit(' . $second->getKey() . ')'));
    $page->wait(1);
    $page->assertValue(groupModal(' input[wire\\:model="productConfigurationGroupForm.name"]'), 'Second Group')
        ->assertDontSee('Browser Option Product');

    $page->click(groupModal(' button:has-text("Add option")'));
    waitForElement($page, itemModal());
    $page->assertValue(itemModal(' input[wire\\:model="productConfigurationGroupItemForm.surcharge"]'), '');
    $page->click(itemModal(' button:has-text("Cancel")'));
    $page->click(groupModal(' button:has-text("Cancel")'));

    $page->script(configurationGroupsComponent('$wire.edit(' . $group->getKey() . ')'));
    $page->wait(1);
    $page->assertValue(groupModal(' input[wire\\:model="productConfigurationGroupForm.name"]'), 'Browser Group')
        ->assertSee('Browser Option Product');

    $page->script(configurationGroupsComponent('$wire.editItem(' . $item->getKey() . ')'));
    $page->wait(1);
    $page->assertValue(itemModal(' input[wire\\:model="productConfigurationGroupItemForm.surcharge"]'), '12.5');
});
