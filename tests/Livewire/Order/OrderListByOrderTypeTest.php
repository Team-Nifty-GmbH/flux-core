<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\OrderListByOrderType;
use FluxErp\Models\OrderType;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    Livewire::test(OrderListByOrderType::class, ['orderType' => $orderType->id])
        ->assertStatus(200);
});
