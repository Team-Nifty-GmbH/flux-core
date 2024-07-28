<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Units;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class UnitsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Units::class)
            ->assertStatus(200);
    }
}
