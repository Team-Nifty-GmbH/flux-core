<?php

use FluxErp\Actions\CalendarEvent\CreateCalendarEvent;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;

beforeEach(function (): void {
    $this->calendar = Calendar::factory()->create();
});

test('create calendar event', function (): void {
    $event = CreateCalendarEvent::make([
        'calendar_id' => $this->calendar->getKey(),
        'title' => 'Team Meeting',
        'start' => '2026-04-10 09:00:00',
        'end' => '2026-04-10 10:00:00',
    ])->validate()->execute();

    expect($event)->toBeInstanceOf(CalendarEvent::class)
        ->title->toBe('Team Meeting');
});

test('create calendar event requires calendar_id title start end', function (): void {
    CreateCalendarEvent::assertValidationErrors([], ['calendar_id', 'title', 'start', 'end']);
});

test('create calendar event end must be after start', function (): void {
    CreateCalendarEvent::assertValidationErrors([
        'calendar_id' => $this->calendar->getKey(),
        'title' => 'Invalid',
        'start' => '2026-04-10 10:00:00',
        'end' => '2026-04-10 09:00:00',
    ], 'end');
});
