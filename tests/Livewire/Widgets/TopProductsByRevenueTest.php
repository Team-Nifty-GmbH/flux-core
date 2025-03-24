<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\TopProductsByRevenue;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TopProductsByRevenueTest extends TestCase
{
    protected string $livewireComponent = TopProductsByRevenue::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
