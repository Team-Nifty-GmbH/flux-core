<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\CustomEvents;
use FluxErp\Models\Order;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CustomEventsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(CustomEvents::class, ['model' => Order::class])
            ->assertStatus(200);
    }
}
