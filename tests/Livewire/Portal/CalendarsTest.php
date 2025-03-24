<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Calendars;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CalendarsTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Calendars::class)
            ->assertStatus(200);
    }
}
