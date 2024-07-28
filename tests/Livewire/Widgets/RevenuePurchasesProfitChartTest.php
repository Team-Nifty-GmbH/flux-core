<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\RevenuePurchasesProfitChart;
use Livewire\Livewire;
use Tests\TestCase;

class RevenuePurchasesProfitChartTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(RevenuePurchasesProfitChart::class)
            ->assertStatus(200);
    }
}
