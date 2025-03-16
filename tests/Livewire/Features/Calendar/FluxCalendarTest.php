<?php

namespace FluxErp\Tests\Livewire\Features\Calendar;

use FluxErp\Livewire\Features\Calendar\FluxCalendar;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class FluxCalendarTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::actingAs($this->user)
            ->test(FluxCalendar::class)
            ->assertStatus(200);
    }
}
