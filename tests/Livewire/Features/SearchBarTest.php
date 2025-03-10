<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\SearchBar;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class SearchBarTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(SearchBar::class)
            ->assertStatus(200);
    }
}
