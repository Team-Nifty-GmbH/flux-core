<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\RevenueByTopCustomers;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RevenueByTopCustomersTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(RevenueByTopCustomers::class)
            ->assertStatus(200);
    }
}
