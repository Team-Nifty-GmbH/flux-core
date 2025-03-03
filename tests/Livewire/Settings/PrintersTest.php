<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Printers;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PrintersTest extends TestCase
{
    protected string $livewireComponent = Printers::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
