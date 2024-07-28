<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\VatRates;
use Livewire\Livewire;
use Tests\TestCase;

class VatRatesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(VatRates::class)
            ->assertStatus(200);
    }
}
