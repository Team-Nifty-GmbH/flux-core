<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\TotalRevenue;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TotalRevenueTest extends TestCase
{
    protected string $livewireComponent = TotalRevenue::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
