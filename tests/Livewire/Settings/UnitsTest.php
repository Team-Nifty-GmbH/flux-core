<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\Units;
use Livewire\Livewire;
use Tests\TestCase;

class UnitsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Units::class)
            ->assertStatus(200);
    }
}
