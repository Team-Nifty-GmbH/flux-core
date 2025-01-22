<?php

namespace FluxErp\Tests\Livewire\DataTablesFeatures\Calendar;

use FluxErp\Livewire\Features\Calendar\CalendarOverview;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CalendarOverviewTest extends TestCase
{
    protected string $livewireComponent = CalendarOverview::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
