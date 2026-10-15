<?php

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Order\OrderPositions;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use Livewire\Livewire;
use function Livewire\invade;

require_once __DIR__ . '/../../Feature/ProductConfiguration/Helpers.php';

beforeEach(function (): void {
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->priceList = PriceList::factory()->create(['is_net' => true]);
    $this->order = configurationOrder($this->priceList);
    $this->server = configurationProduct(['is_configurable' => true, 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->server, $this->priceList, '100');
    $this->storage = configurationGroup(['name' => 'Storage']);
    $this->ssd = configurationItem(
        $this->storage,
        configurationProduct(['name' => 'SSD']),
        ['surcharge' => 20, 'sort' => 0]
    );
    $this->hdd = configurationItem(
        $this->storage,
        configurationProduct(['name' => 'HDD']),
        ['surcharge' => 0, 'sort' => 1]
    );
    attachConfigurationGroup($this->server, $this->storage, isRequired: true);
    $this->server->productConfigurationDefaults()->create([
        'product_configuration_group_item_id' => $this->hdd->getKey(),
        'amount' => 1,
    ]);

    $this->orderForm = new OrderForm(Livewire::new(OrderPositions::class), 'order');
    $this->orderForm->fill($this->order->fresh(['currency']));
});

test('choosing a configurable product loads groups and defaults', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('changedProductId', $this->server->getKey())
        ->assertSet('orderPosition.is_configurable', true)
        ->assertSet('orderPosition.configuration', [
            ['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1],
        ])
        ->assertSet('configurationGroups.0.name', 'Storage')
        ->assertSet('configurationGroups.0.items.0.name', 'SSD');

    expect(bccomp($component->get('configurationGroups.0.items.0.price'), '20', 2))->toBe(0);
});

test('the preview prices the configuration and reports rule violations', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('changedProductId', $this->server->getKey())
        ->set('orderPosition.configuration', [
            ['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1],
        ])
        ->call('previewConfiguration')
        ->assertReturned(fn (array $result) => bccomp($result['total_price'], '120', 2) === 0);

    $component->set('orderPosition.configuration', [])
        ->call('previewConfiguration')
        ->assertReturned(['errors' => ['Storage requires a selection.']]);
});

test('saving creates the configured position', function (): void {
    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('changedProductId', $this->server->getKey())
        ->set('orderPosition.configuration', [
            ['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1],
        ])
        ->call('addOrderPosition')
        ->assertReturned(true);

    $parent = OrderPosition::query()->where('product_id', $this->server->getKey())->sole();

    expect($parent->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]]);
});

test('change configuration loads the current children and replaces them on save', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('changedProductId', $this->server->getKey())
        ->call('addOrderPosition');
    $parent = OrderPosition::query()->where('product_id', $this->server->getKey())->sole();

    $component->call('editOrderPosition', $parent->getKey())
        ->assertSet('orderPosition.is_configurable', true)
        ->call('editConfiguration')
        ->assertSet('orderPosition.configuration', [
            ['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1],
        ])
        ->set('orderPosition.configuration', [
            ['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1],
        ])
        ->call('addOrderPosition')
        ->assertReturned(true);

    expect($parent->refresh()->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]]);
});

test('rows carry the option id so children hide the edit button', function (): void {
    $instance = Livewire::test(OrderPositions::class, ['order' => $this->orderForm])->instance();

    expect(invade($instance)->getReturnKeys())->toContain('product_configuration_group_item_id')
        ->and(collect(invade($instance)->getRowActions())->first()->attributes['x-show'] ?? null)
        ->toContain('record.product_configuration_group_item_id');
});
