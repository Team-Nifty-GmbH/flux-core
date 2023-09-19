<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\SearchBar;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SearchBarTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(SearchBar::class)
            ->assertStatus(200);
    }
}
