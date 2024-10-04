<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Watchlists;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WatchlistTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Watchlists::class)
            ->assertStatus(200);
    }
}
