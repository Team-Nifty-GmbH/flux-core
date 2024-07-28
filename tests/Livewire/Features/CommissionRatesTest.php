<?php

namespace Tests\Feature\Livewire\Features;

use FluxErp\Livewire\Features\CommissionRates;
use Livewire\Livewire;
use Tests\TestCase;

class CommissionRatesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CommissionRates::class)
            ->assertStatus(200);
    }
}
