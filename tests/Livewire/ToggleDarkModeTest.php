<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\ToggleDarkMode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ToggleDarkModeTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(ToggleDarkMode::class)
            ->assertStatus(200);
    }
}
