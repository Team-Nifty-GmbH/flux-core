<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\CustomEvents;
use FluxErp\Models\Order;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class CustomEventsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(CustomEvents::class, ['model' => Order::class])
            ->assertStatus(200);
    }
}
