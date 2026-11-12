<?php

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\ProductConfigurationRule;

require_once __DIR__ . '/../../Feature/ProductConfiguration/Helpers.php';

test('configure a product in an order, see a rule violation and save', function (): void {
    $priceList = PriceList::factory()->create(['is_net' => true]);
    $order = configurationOrder($priceList);
    $server = configurationProduct(['name' => 'Browser Server', 'is_configurable' => true]);
    configurationPrice($server, $priceList, '100');

    $storage = configurationGroup(['name' => 'Storage']);
    $hdd = configurationItem($storage, configurationProduct(['name' => 'Plain HDD']), ['surcharge' => 0]);
    $ssd = configurationItem($storage, configurationProduct(['name' => 'Fast SSD']), ['surcharge' => 20]);
    $extras = configurationGroup([
        'name' => 'Extras',
        'selection_type_enum' => ConfigurationSelectionTypeEnum::Multiple,
    ]);
    $heater = configurationItem($extras, configurationProduct(['name' => 'Heater']), ['surcharge' => 5]);
    attachConfigurationGroup($server, $storage, isRequired: true);
    attachConfigurationGroup($server, $extras, sort: 1);
    $server->productConfigurationDefaults()->create([
        'product_configuration_group_item_id' => $hdd->getKey(),
        'amount' => 1,
    ]);
    $rule = ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $heater->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Excludes,
    ]);
    $rule->targetItems()->attach($ssd->getKey());

    $page = visit(route('orders.id', $order))->assertNoSmoke();
    waitForDataTable($page);

    $page->script(sprintf(<<<'JS'
        () => {
            const component = Livewire.all().find((item) => item.name === 'order.order-positions');

            return component.$wire.editOrderPosition()
                .then(() => component.$wire.changedProductId(%d));
        }
    JS, $server->getKey()));

    waitForElement($page, '#configurator-item-' . $ssd->getKey());
    $page->click('#configurator-item-' . $ssd->getKey());
    $page->fill('#configurator-amount-' . $heater->getKey(), '1');
    $page->wait(1)
        ->assertSee('Heater cannot be combined with Fast SSD.');

    $page->fill('#configurator-amount-' . $heater->getKey(), '0');
    $page->wait(1)
        ->assertDontSee('Heater cannot be combined with Fast SSD.');

    $page->click('#configurator-save');
    waitForDataTable($page);
    $page->assertSee('Fast SSD');

    $parent = OrderPosition::query()->where('product_id', $server->getKey())->sole();

    expect($parent->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $ssd->getKey(), 'amount' => 1]]);
});
