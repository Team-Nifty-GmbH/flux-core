<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Calendars;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class CalendarsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Calendars::class)
            ->assertStatus(200);
    }
}
