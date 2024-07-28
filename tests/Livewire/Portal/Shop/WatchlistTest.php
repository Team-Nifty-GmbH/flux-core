<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Watchlist;
use Livewire\Livewire;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Watchlist::class)
            ->assertStatus(200);
    }
}
