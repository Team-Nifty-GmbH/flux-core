<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\BankConnections;
use Livewire\Livewire;
use Tests\TestCase;

class BankConnectionsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(BankConnections::class)
            ->assertStatus(200);
    }
}
