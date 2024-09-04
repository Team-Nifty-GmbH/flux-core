<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\AverageOrderValue;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AverageOrderValueTest extends TestCase
{
    protected string $livewireComponent = AverageOrderValue::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
