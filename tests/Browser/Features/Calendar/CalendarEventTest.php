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

test('clicking a new date resets the event form', function (): void {
    createCalendarWithOwner();

    $page = visitCalendar();

    // First: open modal via date-click and fill title
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

    // Type a title into the input
    $page->script(<<<'JS'
        () => {
            const input = document.querySelector('[x-ref="autofocus"]');
            if (!input) throw new Error('Title input not found');
            input.value = 'First Event Title';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    // Wait for Livewire to process the input
    $page->script('() => new Promise(r => setTimeout(r, 1000))');

    // Close the modal
    $page->script(<<<'JS'
        () => { $modalClose('edit-event-modal'); }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 500))');

    // Second: click a different date
    $newStart = Carbon::tomorrow()->addDay()->setTime(10, 0);
    $page->script(<<<JS
        () => {
            window.Livewire.dispatch('calendar-date-click', {
                allDay: false,
                dateStr: '{$newStart->toIso8601String()}',
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

    // Wait for Livewire roundtrip to complete
    $page->script('() => new Promise(r => setTimeout(r, 2000))');

    // Read the title — it should be empty, not "First Event Title"
    $title = $page->script(<<<'JS'
        () => {
            const modal = document.querySelector('#edit-event-modal');
            if (!modal) return 'no-modal';
            const wireEl = modal.querySelector('[wire\\:id]');
            if (!wireEl || !wireEl.__livewire) return 'no-livewire';
            return wireEl.__livewire.$wire?.event?.title || '';
        }
    JS);

    expect($title)->toBe('', "Event title was not reset — still contains data from previous event");

    $page->assertNoJavascriptErrors();
});

test('clicking different events shows correct data without one-behind lag', function (): void {
    $calendar = createCalendarWithOwner(['has_repeatable_events' => true]);

    $startA = Carbon::tomorrow()->setTime(10, 0);
    $eventA = CalendarEvent::factory()->create([
        'calendar_id' => $calendar->getKey(),
        'title' => 'Event Alpha',
        'start' => $startA,
        'end' => $startA->copy()->addHour(),
        'is_all_day' => false,
    ]);

    $startB = Carbon::tomorrow()->setTime(14, 0);
    $eventB = CalendarEvent::factory()->create([
        'calendar_id' => $calendar->getKey(),
        'title' => 'Event Beta',
        'start' => $startB,
        'end' => $startB->copy()->addHour(),
        'is_all_day' => false,
    ]);

    $page = visitCalendar();

    $calendarId = $calendar->getKey();

    // Click event A
    $page->script(<<<JS
        () => {
            window.Livewire.dispatch('calendar-event-click', {
                event: {
                    id: '{$eventA->getKey()}',
                    title: 'Event Alpha',
                    start: '{$eventA->start->toIso8601String()}',
                    end: '{$eventA->end->toIso8601String()}',
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

    waitForElement($page, '#edit-event-modal');

    // Read title from child component — should be Event Alpha
    $titleA = $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => resolve('timeout'), 10000);
            const check = () => {
                const modal = document.querySelector('#edit-event-modal');
                if (!modal) { setTimeout(check, 200); return; }
                const wireEl = modal.querySelector('[wire\\:id]');
                if (!wireEl || !wireEl.__livewire) { setTimeout(check, 200); return; }
                const title = wireEl.__livewire.$wire?.event?.title;
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

    expect($titleA)->toBe('Event Alpha');

    // Close modal
    $page->script("() => { \$modalClose('edit-event-modal'); }");
    $page->script('() => new Promise(r => setTimeout(r, 500))');

    // Click event B
    $page->script(<<<JS
        () => {
            window.Livewire.dispatch('calendar-event-click', {
                event: {
                    id: '{$eventB->getKey()}',
                    title: 'Event Beta',
                    start: '{$eventB->start->toIso8601String()}',
                    end: '{$eventB->end->toIso8601String()}',
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

    waitForElement($page, '#edit-event-modal');

    // Read title — should be Event Beta, NOT Event Alpha
    $titleB = $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => resolve('timeout'), 10000);
            const check = () => {
                const modal = document.querySelector('#edit-event-modal');
                if (!modal) { setTimeout(check, 200); return; }
                const wireEl = modal.querySelector('[wire\\:id]');
                if (!wireEl || !wireEl.__livewire) { setTimeout(check, 200); return; }
                const title = wireEl.__livewire.$wire?.event?.title;
                if (title && title !== 'Event Alpha') {
                    clearTimeout(timeout);
                    resolve(title);
                } else if (title === 'Event Alpha') {
                    clearTimeout(timeout);
                    resolve('Event Alpha');
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    expect($titleB)->toBe('Event Beta', 'One-behind bug: modal shows previous event data instead of current');

    $page->assertNoJavascriptErrors();
});
