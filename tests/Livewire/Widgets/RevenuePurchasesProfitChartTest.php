<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\RevenuePurchasesProfitChart;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RevenuePurchasesProfitChartTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(RevenuePurchasesProfitChart::class)
            ->assertStatus(200);
    }
}
