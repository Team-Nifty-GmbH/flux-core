<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Warehouses;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WarehousesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Warehouses::class)
            ->assertStatus(200);
    }
}
