<?php

namespace Tests\Feature\Livewire\Order;

use FluxErp\Livewire\Order\OrderListByOrderType;
use FluxErp\Models\OrderType;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OrderListByOrderTypeTest extends TestCase
{
    protected string $livewireComponent = OrderListByOrderType::class;

    public function test_renders_successfully()
    {
        $orderType = OrderType::factory()->create();

        Livewire::test($this->livewireComponent, ['orderType' => $orderType->id])
            ->assertStatus(200);
    }
}
