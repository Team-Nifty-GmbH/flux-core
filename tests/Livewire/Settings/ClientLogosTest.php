<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\ClientLogos;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ClientLogosTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(ClientLogos::class)
            ->assertStatus(200);
    }
}
