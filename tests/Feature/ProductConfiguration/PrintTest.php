<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use Illuminate\Support\Number;

require_once __DIR__ . '/Helpers.php';

test('configuration children print amount and price, a zero surcharge prints no price', function (): void {
    $priceList = PriceList::factory()->create(['is_net' => true]);
    $order = configurationOrder($priceList);
    $server = configurationProduct(['is_configurable' => true]);
    configurationPrice($server, $priceList, '100');
    $group = configurationGroup(['selection_type_enum' => 'multiple']);
    $free = configurationItem($group, configurationProduct(['name' => 'Free Cable']), ['surcharge' => 0]);
    $paid = configurationItem($group, configurationProduct(['name' => 'Paid Fan']), ['surcharge' => 25]);
    attachConfigurationGroup($server, $group);

    $parent = CreateOrderPosition::make([
        'order_id' => $order->getKey(),
        'product_id' => $server->getKey(),
        'amount' => 1,
        'configuration' => [
            ['product_configuration_group_item_id' => $free->getKey(), 'amount' => 1],
            ['product_configuration_group_item_id' => $paid->getKey(), 'amount' => 1],
        ],
    ])->validate()->execute();

    $render = function (OrderPosition $position): string {
        $position->depth = $position->parent_id ? 1 : 0;

        return view('flux::components.print.order.order-position', [
            'position' => $position,
            'isNet' => true,
        ])->render();
    };
    $children = OrderPosition::query()->where('parent_id', $parent->getKey())->get()->keyBy('product_configuration_group_item_id');

    $freeHtml = $render($children->get($free->getKey()));
    $paidHtml = $render($children->get($paid->getKey()));

    expect($freeHtml)->toContain('Free Cable')
        ->not->toContain(Number::currency(0))
        ->and($paidHtml)->toContain('Paid Fan')
        ->toContain(Number::currency(25))
        ->and($render($parent))->toContain(Number::currency(100));
});
