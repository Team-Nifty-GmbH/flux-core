<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\VatRates;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class VatRatesTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(VatRates::class)
            ->assertStatus(200);
    }
}
