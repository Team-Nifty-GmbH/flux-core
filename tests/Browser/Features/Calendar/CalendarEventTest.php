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

    waitForElement($page, '[calendar-root] .fc', 10000);

    return $page;
}

test('calendar page loads without js errors', function (): void {
    createCalendarWithOwner();

    visitCalendar()->assertNoJavascriptErrors();
});

test('event edit modal renders exactly one calendar-event component', function (): void {
    createCalendarWithOwner();

    $page = visitCalendar();

    $count = $page->script(<<<'JS'
        () => new Promise((resolve) => {
            const timeout = setTimeout(() => {
                const modal = document.querySelector('#edit-event-modal');
                if (!modal) {
                    resolve('no-modal');
                    return;
                }
                resolve(modal.querySelectorAll('[wire\\:name="features.calendar.calendar-event"]').length);
            }, 5000);
            const check = () => {
                const modal = document.querySelector('#edit-event-modal');
                if (!modal) { setTimeout(check, 200); return; }
                const count = modal.querySelectorAll('[wire\\:name="features.calendar.calendar-event"]').length;
                if (count > 0) {
                    clearTimeout(timeout);
                    setTimeout(() => {
                        resolve(modal.querySelectorAll('[wire\\:name="features.calendar.calendar-event"]').length);
                    }, 1000);
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    expect($count)->toBe(1, 'edit-event-modal must contain exactly one features.calendar.calendar-event component, found: ' . $count);
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

    $page->wait(1)
        ->assertNoJavascriptErrors();
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

    waitForElement($page, '#edit-event-modal');

    $page->assertNoJavascriptErrors();

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
            input.value = 'First Event Title';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    $page->wait(1);

    $page->script(<<<'JS'
        () => { $tsui.close.modal('edit-event-modal'); }
    JS);

    $page->wait(0.5);

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

    $title = $page->script(<<<'JS'
        () => {
            const modal = document.querySelector('#edit-event-modal');
            if (!modal) return 'no-modal';
            const wireEl = modal.querySelector('[wire\\:id]');
            if (!wireEl || !wireEl.__livewire) return 'no-livewire';
            return wireEl.__livewire.$wire?.event?.title || '';
        }
    JS);

    expect($title)->toBe('', 'Event title was not reset — still contains data from previous event');

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

    $page->script("() => { \$tsui.close.modal('edit-event-modal'); }");
    $page->wait(0.5);

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

test('dragging a recurring calendar event opens confirm dialog with save options', function (): void {
    $calendar = createCalendarWithOwner(['has_repeatable_events' => true]);

    $start = Carbon::tomorrow()->setTime(10, 0);
    $event = CalendarEvent::factory()->create([
        'calendar_id' => $calendar->getKey(),
        'title' => 'Recurring Event',
        'start' => $start,
        'end' => $start->copy()->addHour(),
        'is_all_day' => false,
    ]);

    $page = visitCalendar();

    $eventId = $event->getKey();
    $calendarId = $calendar->getKey();
    $newStart = $start->copy()->addDay();
    $newEnd = $newStart->copy()->addHour();

    // Simulate drag-drop of a recurring event instance: id "<id>|<repetition>"
    // signals to the form that the dragged event was repeatable.
    $page->script(<<<JS
        () => {
            window.Livewire.dispatch('calendar-event-change', {
                event: {
                    id: '{$eventId}|0',
                    title: 'Recurring Event',
                    start: '{$newStart->toIso8601String()}',
                    end: '{$newEnd->toIso8601String()}',
                    allDay: false,
                    extendedProps: {
                        is_editable: true,
                        is_repeatable: true,
                        has_repeats: true,
                        calendar_type: null,
                        calendar_id: {$calendarId}
                    }
                },
                trigger: 'event-change'
            });
        }
    JS);

    waitForElement($page, '#confirm-dialog');
    waitForElement($page, '#future-event-radio');
    waitForElement($page, '#all-event-radio');

    $page->assertNoJavascriptErrors();

    expect($event->fresh()->start->toDateTimeString())
        ->toBe($start->toDateTimeString(), 'Event must not be saved before user confirms which occurrences to update');
});
