<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Watchlist;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WatchlistTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Watchlist::class)
            ->assertStatus(200);
    }
}
