<?php

namespace Tests\Feature\Livewire\Charts;

use FluxErp\Livewire\Charts\Chart;
use Livewire\Livewire;
use Tests\TestCase;

class ChartTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Chart::class)
            ->assertStatus(200);
    }
}
