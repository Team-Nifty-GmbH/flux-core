<?php

use FluxErp\Livewire\Features\Calendar\CalendarEvent;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CalendarEvent::class)
        ->assertOk();
});
