<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\ToggleDarkMode;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ToggleDarkModeTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ToggleDarkMode::class)
            ->assertStatus(200);
    }
}
