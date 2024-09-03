<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\OpenDeliveries;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OpenDeliveriesTest extends TestCase
{
    protected string $livewireComponent = OpenDeliveries::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
