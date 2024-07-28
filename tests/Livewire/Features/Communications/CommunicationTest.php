<?php

namespace Tests\Feature\Livewire\Features\Communications;

use FluxErp\Livewire\Features\Communications\Communication;
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
