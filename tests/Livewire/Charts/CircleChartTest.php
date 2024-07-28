<?php

namespace Tests\Feature\Livewire\Charts;

use FluxErp\Livewire\Charts\CircleChart;
use Livewire\Livewire;
use Tests\TestCase;

class CircleChartTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CircleChart::class)
            ->assertStatus(200);
    }
}
