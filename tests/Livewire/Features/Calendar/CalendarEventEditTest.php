<?php

use FluxErp\Livewire\Features\Calendar\CalendarEventEdit;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CalendarEventEdit::class)
        ->assertOk();
});
