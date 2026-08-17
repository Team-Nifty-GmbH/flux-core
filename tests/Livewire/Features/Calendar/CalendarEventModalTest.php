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
