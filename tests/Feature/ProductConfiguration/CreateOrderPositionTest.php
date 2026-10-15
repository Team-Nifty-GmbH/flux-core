<?php

use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->priceList = PriceList::factory()->create(['is_net' => true]);
    $this->order = configurationOrder($this->priceList);

    $this->server = configurationProduct(['is_configurable' => true, 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->server, $this->priceList, '100');

    $this->storage = configurationGroup(['name' => 'Storage']);
    $this->ssd = configurationItem(
        $this->storage,
        configurationProduct(['name' => 'SSD', 'vat_rate_id' => $this->vatRate->getKey()]),
        ['surcharge' => 10, 'max_amount' => 4]
    );
    $this->hdd = configurationItem(
        $this->storage,
        configurationProduct(['name' => 'HDD', 'vat_rate_id' => $this->vatRate->getKey()]),
        ['surcharge' => 0]
    );
    attachConfigurationGroup($this->server, $this->storage, isRequired: true);
    $this->server->productConfigurationDefaults()->create([
        'product_configuration_group_item_id' => $this->hdd->getKey(),
        'amount' => 1,
    ]);

    $this->create = fn (array $data = []) => CreateOrderPosition::make(array_merge([
        'order_id' => $this->order->getKey(),
        'product_id' => $this->server->getKey(),
        'amount' => 3,
    ], $data))->validate()->execute();
});

test('a configuration creates priced children under the parent', function (): void {
    $parent = ($this->create)([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]],
    ]);

    $child = OrderPosition::query()->where('parent_id', $parent->getKey())->sole();

    expect(bccomp($parent->total_net_price, '300', 2))->toBe(0)
        ->and($child->product_id)->toBe($this->ssd->product_id)
        ->and($child->product_configuration_group_item_id)->toBe($this->ssd->getKey())
        ->and(bccomp($child->amount_bundle, '2', 2))->toBe(0)
        ->and(bccomp($child->amount, '6', 2))->toBe(0)
        ->and(bccomp($child->unit_net_price, '10', 2))->toBe(0)
        ->and(bccomp($child->unit_price, '10', 2))->toBe(0)
        ->and(bccomp($child->total_net_price, '60', 2))->toBe(0)
        ->and($child->is_bundle_position)->toBeFalse();
});

test('without configuration the defaults are used', function (): void {
    $parent = ($this->create)();

    expect($parent->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1]]);
});

test('the order total counts parent and children', function (): void {
    ($this->create)([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]],
    ]);

    $this->order->calculatePrices()->save();

    expect(bccomp($this->order->refresh()->total_net_price, '360', 2))->toBe(0);
});

test('an invalid configuration blocks the position', function (): void {
    expect(fn () => ($this->create)([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 5]],
    ]))->toThrow(Illuminate\Validation\ValidationException::class, 'SSD needs an amount between 1 and 4.');

    expect(OrderPosition::query()->where('order_id', $this->order->getKey())->count())->toBe(0);
});

test('configuration is forbidden for a normal product', function (): void {
    CreateOrderPosition::assertValidationErrors([
        'order_id' => $this->order->getKey(),
        'product_id' => $this->ssd->product_id,
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]],
    ], 'configuration');
});

test('defaults with a deleted product block the position instead of creating a child for it', function (): void {
    $this->hdd->product->delete();

    expect(fn () => ($this->create)())
        ->toThrow(Illuminate\Validation\ValidationException::class, 'HDD is no longer available.');
});

test('creating a position whose configuration total is negative fails', function (): void {
    $this->ssd->update(['surcharge' => -60]);

    expect(fn () => ($this->create)([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]],
    ]))->toThrow(Illuminate\Validation\ValidationException::class, 'The configuration total must not be negative.');
});

test('a child cannot be added to a configured position by hand', function (): void {
    $parent = ($this->create)();

    CreateOrderPosition::assertValidationErrors([
        'order_id' => $this->order->getKey(),
        'parent_id' => $parent->getKey(),
        'product_id' => $this->ssd->product_id,
    ], 'parent_id');
});

test('replicating an order copies the configured position once', function (): void {
    ($this->create)([
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]],
    ]);

    $copy = ReplicateOrder::make([
        'id' => $this->order->getKey(),
        'address_invoice_id' => $this->order->address_invoice_id,
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
    ])->validate()->execute();

    $positions = OrderPosition::query()->where('order_id', $copy->getKey())->get();
    $parent = $positions->firstWhere('product_id', $this->server->getKey());

    expect($positions)->toHaveCount(2)
        ->and($parent->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]]);
});
