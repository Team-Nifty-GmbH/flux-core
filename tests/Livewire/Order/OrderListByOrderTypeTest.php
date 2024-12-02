<?php

namespace Tests\Feature\Livewire\Order;

use FluxErp\Livewire\Order\OrderListByOrderType;
use FluxErp\Models\OrderType;
use FluxErp\Tests\Livewire\BaseSetup;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OrderListByOrderTypeTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        Livewire::test(OrderListByOrderType::class, ['orderType' => $orderType->id])
            ->assertStatus(200);
    }
}
