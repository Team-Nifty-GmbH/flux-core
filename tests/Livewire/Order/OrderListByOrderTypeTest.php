<?php

namespace FluxErp\Tests\Livewire\DataTablesOrder;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\OrderListByOrderType;
use FluxErp\Models\OrderType;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderListByOrderTypeTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        Livewire::test(OrderListByOrderType::class, ['orderType' => $orderType->id])
            ->assertStatus(200);
    }
}
