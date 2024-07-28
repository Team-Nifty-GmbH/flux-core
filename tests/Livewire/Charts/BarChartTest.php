<?php

namespace Tests\Feature\Livewire\Charts;

use FluxErp\Livewire\Charts\BarChart;
use Livewire\Livewire;
use Tests\TestCase;

class BarChartTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(BarChart::class)
            ->assertStatus(200);
    }
}
