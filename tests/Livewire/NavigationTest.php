<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Navigation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class NavigationTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Navigation::class)
            ->assertStatus(200);
    }
}
