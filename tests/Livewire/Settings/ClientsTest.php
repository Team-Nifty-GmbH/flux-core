<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Clients;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ClientsTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Clients::class)
            ->assertStatus(200);
    }
}
