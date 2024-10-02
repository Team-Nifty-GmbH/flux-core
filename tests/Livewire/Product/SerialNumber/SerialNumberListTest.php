<?php

namespace Tests\Feature\Livewire\Product\SerialNumber;

use FluxErp\Livewire\Product\SerialNumber\SerialNumberList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SerialNumberListTest extends TestCase
{
    protected string $livewireComponent = SerialNumberList::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
