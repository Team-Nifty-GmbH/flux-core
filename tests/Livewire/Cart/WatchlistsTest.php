<?php

namespace FluxErp\Tests\Livewire\Cart;

use FluxErp\Livewire\Cart\Watchlists;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WatchlistsTest extends TestCase
{
    protected string $livewireComponent = Watchlists::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
