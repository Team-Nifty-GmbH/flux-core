<?php

namespace FluxErp\Tests\Livewire\Calendars;

use FluxErp\Livewire\Calendars\Calendar;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class CalendarTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Calendar::class)
            ->assertStatus(200);
    }
}
