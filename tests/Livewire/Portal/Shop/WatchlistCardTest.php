<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\WatchlistCard;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WatchlistCardTest extends TestCase
{
    protected string $livewireComponent = WatchlistCard::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
