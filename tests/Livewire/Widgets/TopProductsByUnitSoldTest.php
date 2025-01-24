<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\TopProductsByUnitSold;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TopProductsByUnitSoldTest extends TestCase
{
    protected string $livewireComponent = TopProductsByUnitSold::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
