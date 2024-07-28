<?php

namespace Tests\Feature\Livewire\Address;

use FluxErp\Livewire\Address\Communication;
use Livewire\Livewire;
use Tests\TestCase;

class CommunicationTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Communication::class)
            ->assertStatus(200);
    }
}
