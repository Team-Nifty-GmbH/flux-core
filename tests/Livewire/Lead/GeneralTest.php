<?php

namespace FluxErp\Tests\Livewire\Lead;

use FluxErp\Livewire\Lead\General;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class GeneralTest extends TestCase
{
    protected string $livewireComponent = General::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
