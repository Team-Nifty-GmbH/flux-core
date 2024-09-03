<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\Purchase;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PurchaseTest extends TestCase
{
    protected string $livewireComponent = Purchase::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
