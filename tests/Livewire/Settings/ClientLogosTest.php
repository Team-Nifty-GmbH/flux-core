<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\ClientLogos;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ClientLogosTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ClientLogos::class)
            ->assertStatus(200);
    }
}
