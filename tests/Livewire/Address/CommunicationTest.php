<?php

namespace FluxErp\Tests\Livewire\Address;

use FluxErp\Livewire\Address\Communication;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommunicationTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Communication::class)
            ->assertStatus(200);
    }
}
