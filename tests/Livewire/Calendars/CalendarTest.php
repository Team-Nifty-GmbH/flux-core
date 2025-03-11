<?php

namespace FluxErp\Tests\Livewire\Calendars;

use FluxErp\Livewire\Calendars\Calendar;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CalendarTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Calendar::class)
            ->assertStatus(200);
    }
}
