<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TicketTypesTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(TicketTypes::class)
            ->assertStatus(200);
    }
}
