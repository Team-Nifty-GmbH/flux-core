<?php

use FluxErp\Livewire\Features\Calendar\Calendar;
use FluxErp\Livewire\Features\Calendar\CalendarEventEdit;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CalendarEventEdit::class)
        ->assertOk();
});

test('renders after the calendar skipped a non editable event change', function (): void {
    Livewire::test(Calendar::class)
        ->call('editEvent', ['extendedProps' => ['is_editable' => false]], 'event-change')
        ->assertOk();

    Livewire::test(CalendarEventEdit::class)
        ->assertOk()
        ->assertSee('edit-event-modal');
});
