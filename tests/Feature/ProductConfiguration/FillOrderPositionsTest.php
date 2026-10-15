<?php

use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use Illuminate\Validation\ValidationException;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->priceList = PriceList::factory()->create(['is_net' => true]);
    $this->order = configurationOrder($this->priceList);
    $this->server = configurationProduct(['is_configurable' => true, 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->server, $this->priceList, '100');
    $this->storage = configurationGroup(['name' => 'Storage']);
    $this->ssd = configurationItem($this->storage, configurationProduct(['name' => 'SSD']), ['surcharge' => 10, 'max_amount' => 2]);
    $this->hdd = configurationItem($this->storage, configurationProduct(['name' => 'HDD']), ['surcharge' => 5]);
    attachConfigurationGroup($this->server, $this->storage);
    $this->server->productConfigurationDefaults()->create([
        'product_configuration_group_item_id' => $this->hdd->getKey(),
        'amount' => 1,
    ]);

    $this->fill = fn (array $position, bool $simulate = false) => FillOrderPositions::make([
        'order_id' => $this->order->getKey(),
        'simulate' => $simulate,
        'order_positions' => [array_merge([
            'order_id' => $this->order->getKey(),
            'product_id' => $this->server->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => Warehouse::default()->getKey(),
            'is_net' => true,
            'name' => 'Server',
            'amount' => 2,
            'is_free_text' => false,
        ], $position)],
    ])->validate()->execute();
});

test('fill builds children from the configuration', function (): void {
    ($this->fill)(['configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]]]);

    $child = OrderPosition::query()->whereNotNull('parent_id')->where('order_id', $this->order->getKey())->sole();

    expect($child->product_configuration_group_item_id)->toBe($this->ssd->getKey())
        ->and(bccomp($child->amount, '4', 2))->toBe(0)
        ->and(bccomp($child->total_net_price, '40', 2))->toBe(0);
});

test('fill falls back to defaults and keeps the configuration of an existing position', function (): void {
    ($this->fill)([]);
    $parent = OrderPosition::query()->whereNull('parent_id')->where('order_id', $this->order->getKey())->sole();

    expect($parent->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1]]);

    ($this->fill)(['configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]]]);
    $parent = OrderPosition::query()->whereNull('parent_id')->where('order_id', $this->order->getKey())->sole();
    ($this->fill)(['id' => $parent->getKey(), 'amount' => 3]);

    expect($parent->refresh()->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]])
        ->and(OrderPosition::query()->where('order_id', $this->order->getKey())->count())->toBe(2);
});

test('simulate returns children without saving', function (): void {
    $result = ($this->fill)(
        ['configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]]],
        true
    );

    expect($result[0]['children'])->toHaveCount(1)
        ->and(OrderPosition::query()->where('order_id', $this->order->getKey())->count())->toBe(0);
});

test('fill rejects invalid configurations, children and configuration on normal products', function (): void {
    $errors = fn (array $position): string => json_encode(
        rescue(fn () => ($this->fill)($position), fn (ValidationException $e) => $e->errors(), false)
    );

    expect($errors([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 3]],
    ]))->toContain('SSD needs an amount between 1 and 2.')
        ->and($errors([
            'children' => [['product_id' => $this->ssd->product_id, 'amount' => 1]],
        ]))->toContain('Configurable products take a configuration instead of children.')
        ->and($errors([
            'product_id' => $this->ssd->product_id,
            'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]],
        ]))->toContain('Only configurable products accept a configuration.')
        ->and(OrderPosition::query()->where('order_id', $this->order->getKey())->count())->toBe(0);
});
