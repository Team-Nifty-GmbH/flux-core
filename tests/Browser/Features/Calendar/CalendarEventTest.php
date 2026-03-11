<?php

use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Pivots\Calendarable;
use Illuminate\Support\Carbon;

function createCalendarWithOwner(array $attributes = []): Calendar
{
    $calendar = Calendar::factory()->create(array_merge([
        'has_repeatable_events' => false,
        'is_public' => false,
        'is_group' => false,
    ], $attributes));

    Calendarable::create([
        'calendar_id' => $calendar->getKey(),
        'calendarable_type' => test()->user->getMorphClass(),
        'calendarable_id' => test()->user->getKey(),
        'permission' => 'owner',
    ]);

    return $calendar;
}

function visitCalendar(): mixed
{
    $page = visit(route('calendars'))
        ->assertRoute('calendars')
        ->assertNoSmoke();

    // Wait for FullCalendar to render
    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Calendar did not initialize')), 10000);
            const check = () => {
                if (document.querySelector('[calendar-root] .fc')) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    return $page;
}

function waitForElement(mixed $page, string $selector, int $timeout = 5000): void
{
    $page->script(<<<JS
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Element not found: {$selector}')), {$timeout});
            const check = () => {
                const el = document.querySelector('{$selector}');
                if (el && el.offsetParent !== null) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);
}

test('calendar page loads without js errors', function (): void {
    createCalendarWithOwner();

    visitCalendar()->assertNoJavascriptErrors();
});

test('saving a new calendar event works without js errors', function (): void {
    createCalendarWithOwner();

    $page = visitCalendar();

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

    waitForElement($page, '[x-ref="autofocus"]');

    $page->script(<<<'JS'
        () => {
            const input = document.querySelector('[x-ref="autofocus"]');
            if (!input) throw new Error('Title input not found');
            input.value = 'Browser Test Event';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1000))');

    $page->assertNoJavascriptErrors();
});

test('editing a calendar event opens modal and syncs via $wire.$set', function (): void {
    $calendar = createCalendarWithOwner(['has_repeatable_events' => true]);

    $start = Carbon::tomorrow()->setTime(10, 0);
    $event = CalendarEvent::factory()->create([
        'calendar_id' => $calendar->getKey(),
        'title' => 'LW4 Test Event',
        'start' => $start,
        'end' => $start->copy()->addHour(),
        'is_all_day' => false,
    ]);

    $page = visitCalendar();

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

    // Wait for modal to actually be visible (not just in DOM)
    waitForElement($page, '#edit-event-modal');

    $page->assertNoJavascriptErrors();

    // Verify event data was synced via $wire.$set by reading Livewire component state
    $title = $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => resolve('timeout'), 10000);
            const check = () => {
                const modal = document.querySelector('#edit-event-modal');
                if (!modal) { setTimeout(check, 200); return; }
                const wireEl = modal.querySelector('[wire\\:id]');
                if (!wireEl || !wireEl.__livewire) { setTimeout(check, 200); return; }
                const lw = wireEl.__livewire;
                const title = lw.$wire?.event?.title
                    || lw.$wire?.$get?.('event.title')
                    || null;
                if (title) {
                    clearTimeout(timeout);
                    resolve(title);
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    expect($title)->toContain('LW4 Test Event');
});
