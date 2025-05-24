<?php

namespace FluxErp\Tests\Livewire\Lead;

use FluxErp\Livewire\Lead\Orders;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OrdersTest extends TestCase
{
    protected string $livewireComponent = Orders::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
