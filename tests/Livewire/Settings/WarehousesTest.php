<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\Warehouses;
use Livewire\Livewire;
use Tests\TestCase;

class WarehousesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Warehouses::class)
            ->assertStatus(200);
    }
}
