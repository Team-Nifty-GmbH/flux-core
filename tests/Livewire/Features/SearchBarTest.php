<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\SearchBar;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class SearchBarTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(SearchBar::class)
            ->assertStatus(200);
    }
}
