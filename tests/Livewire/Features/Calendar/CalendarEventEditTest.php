<?php

use FluxErp\Livewire\Features\Calendar\Calendar;
use FluxErp\Livewire\Features\Calendar\CalendarEvent;
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

test('renders the default edit component without edit_component', function (): void {
    Livewire::test(CalendarEventEdit::class)
        ->assertOk()
        ->assertSeeLivewire('features.calendar.calendar-event');
});

test('discards an edit component that is no calendar event component', function (): void {
    Livewire::test(CalendarEventEdit::class)
        ->set('event.edit_component', 'dashboard.dashboard')
        ->assertOk()
        ->assertDontSeeLivewire('dashboard.dashboard');
});

test('discards an unknown edit component', function (): void {
    Livewire::test(CalendarEventEdit::class)
        ->set('event.edit_component', 'does-not-exist')
        ->assertOk();
});

test('renders an edit component extending the calendar event component', function (): void {
    $component = new class() extends CalendarEvent {};
    Livewire::component('custom-calendar-event', $component);

    Livewire::test(CalendarEventEdit::class)
        ->set('event.edit_component', 'custom-calendar-event')
        ->assertOk()
        ->assertSeeLivewire('custom-calendar-event');
});
