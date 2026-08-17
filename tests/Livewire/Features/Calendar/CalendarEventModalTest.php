<?php

use FluxErp\Livewire\Features\Calendar\CalendarEventEdit;
use Livewire\Livewire;

test('an event update still sends the markup that carries the modal', function (): void {
    $component = Livewire::test(CalendarEventEdit::class)
        ->set('event.title', 'Termin');

    $html = data_get($component->effects, 'html');

    expect($html)->not->toBeNull()
        ->and($html)->toContain('edit-event-modal');
});

test('reopening an event of the same edit component still sends the markup', function (): void {
    $component = Livewire::test(CalendarEventEdit::class)
        ->set('event.edit_component', 'features.calendar.calendar-event')
        ->set('event.title', 'Erster Termin')
        ->set('event.title', 'Zweiter Termin');

    $html = data_get($component->effects, 'html');

    expect($html)->not->toBeNull()
        ->and($html)->toContain('edit-event-modal');
});
