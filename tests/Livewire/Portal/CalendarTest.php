<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Calendar;
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
