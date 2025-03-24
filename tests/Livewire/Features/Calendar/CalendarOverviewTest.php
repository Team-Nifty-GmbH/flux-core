<?php

namespace FluxErp\Tests\Livewire\Features\Calendar;

use FluxErp\Livewire\Features\Calendar\CalendarOverview;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CalendarOverviewTest extends TestCase
{
    protected string $livewireComponent = CalendarOverview::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
