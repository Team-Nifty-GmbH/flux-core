<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\OpenDeliveries;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OpenDeliveriesTest extends TestCase
{
    protected string $livewireComponent = OpenDeliveries::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
