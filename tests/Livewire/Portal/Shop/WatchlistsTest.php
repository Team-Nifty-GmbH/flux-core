<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Watchlists;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WatchlistsTest extends TestCase
{
    protected string $livewireComponent = Watchlists::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
