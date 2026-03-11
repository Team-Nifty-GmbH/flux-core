<?php

use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Pivots\Calendarable;
use Illuminate\Support\Carbon;

test('calendar page loads without js errors', function (): void {
    $calendar = Calendar::factory()->create([
        'has_repeatable_events' => false,
        'is_public' => false,
        'is_group' => false,
    ]);

    Calendarable::create([
        'calendar_id' => $calendar->getKey(),
        'calendarable_type' => $this->user->getMorphClass(),
        'calendarable_id' => $this->user->getKey(),
        'permission' => 'owner',
    ]);

    $page = visit(route('calendars'))
        ->assertRoute('calendars')
        ->assertNoSmoke();

    // Wait for calendar to initialize
    $page->script('() => new Promise(r => setTimeout(r, 3000))');

    $page->assertNoJavascriptErrors();
});

test('saving a new calendar event works without js errors', function (): void {
    $calendar = Calendar::factory()->create([
        'has_repeatable_events' => false,
        'is_public' => false,
        'is_group' => false,
    ]);

    Calendarable::create([
        'calendar_id' => $calendar->getKey(),
        'calendarable_type' => $this->user->getMorphClass(),
        'calendarable_id' => $this->user->getKey(),
        'permission' => 'owner',
    ]);

    $page = visit(route('calendars'))
        ->assertRoute('calendars')
        ->assertNoSmoke();

    // Wait for calendar to initialize
    $page->script('() => new Promise(r => setTimeout(r, 3000))');

    // Trigger a date click via Livewire dispatch
    $start = Carbon::tomorrow()->setTime(14, 0);
    $page->script(<<<JS
        () => {
            window.Livewire.dispatch('calendar-date-click', {
                allDay: false,
                dateStr: '{$start->toIso8601String()}',
                view: {
                    type: 'timeGridWeek',
                    dateEnv: {
                        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
                    }
                },
                trigger: 'date-click'
            });
        }
    JS);

    // Wait for modal to open
    $page->script('() => new Promise(r => setTimeout(r, 3000))');

    // Fill in the title via JS (the input might be inside a Livewire component)
    $page->script(<<<'JS'
        () => {
            const input = document.querySelector('[x-ref="autofocus"]')
                || document.querySelector('input[wire\\:model="event.title"]')
                || document.querySelector('input[wire\\:model\\.live="event.title"]');
            if (!input) throw new Error('Title input not found');
            input.value = 'Browser Test Event';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    // Wait for Livewire to process
    $page->script('() => new Promise(r => setTimeout(r, 1000))');

    $page->assertNoJavascriptErrors();
});

test('editing a calendar event with $wire.$set works', function (): void {
    $calendar = Calendar::factory()->create([
        'has_repeatable_events' => true,
        'is_public' => false,
        'is_group' => false,
    ]);

    Calendarable::create([
        'calendar_id' => $calendar->getKey(),
        'calendarable_type' => $this->user->getMorphClass(),
        'calendarable_id' => $this->user->getKey(),
        'permission' => 'owner',
    ]);

    $start = Carbon::tomorrow()->setTime(10, 0);
    $event = CalendarEvent::factory()->create([
        'calendar_id' => $calendar->getKey(),
        'title' => 'LW4 Test Event',
        'start' => $start,
        'end' => $start->copy()->addHour(),
        'is_all_day' => false,
    ]);

    $page = visit(route('calendars'))
        ->assertRoute('calendars')
        ->assertNoSmoke();

    // Wait for calendar to initialize
    $page->script('() => new Promise(r => setTimeout(r, 3000))');

    // Trigger event click with proper FullCalendar-like structure
    $eventId = $event->getKey();
    $calendarId = $calendar->getKey();
    $startIso = $event->start->toIso8601String();
    $endIso = $event->end->toIso8601String();

    $page->script(<<<JS
        () => {
            window.Livewire.dispatch('calendar-event-click', {
                event: {
                    id: '{$eventId}',
                    title: 'LW4 Test Event',
                    start: '{$startIso}',
                    end: '{$endIso}',
                    allDay: false,
                    extendedProps: {
                        is_editable: true,
                        is_repeatable: true,
                        has_repeats: false,
                        calendar_type: null,
                        calendar_id: {$calendarId}
                    }
                },
                trigger: 'event-click'
            });
        }
    JS);

    // Wait for modal to open
    $page->script('() => new Promise(r => setTimeout(r, 3000))');

    // Verify no JS errors from the event edit form rendering
    $page->assertNoJavascriptErrors();

    // Verify the event edit modal is visible
    $modalVisible = $page->script(<<<'JS'
        () => {
            const modal = document.querySelector('[id="edit-event-modal"]');
            return modal ? 'visible' : 'not-found';
        }
    JS);

    expect($modalVisible)->toContain('visible');
});
