<?php

use FluxErp\Livewire\Features\Calendar\Calendar as CalendarComponent;
use FluxErp\Livewire\Features\Calendar\CalendarEvent as CalendarEventComponent;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Task;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs($this->user, 'web');

    $this->calendar = Calendar::factory()->create(['is_public' => false]);
    $this->calendarEvent = CalendarEvent::factory()->create([
        'calendar_id' => $this->calendar->getKey(),
        'title' => 'Event',
        'start' => now(),
        'end' => now()->addHour(),
    ]);
    $this->period = [
        'start' => now()->subDay()->toDateTimeString(),
        'end' => now()->addDay()->toDateTimeString(),
        'startStr' => now()->subDay()->toDateTimeString(),
        'endStr' => now()->addDay()->toDateTimeString(),
    ];
});

test('events load only for listed calendars', function (?string $permission, int $expected): void {
    if ($permission) {
        $this->user->calendars()->attach($this->calendar->getKey(), ['permission' => $permission]);
    }

    $events = Livewire::test(CalendarComponent::class)
        ->instance()
        ->getEvents($this->period, ['id' => $this->calendar->getKey(), 'permission' => 'reader']);

    expect($events)->toHaveCount($expected);
})->with([
    'no membership' => [null, 0],
    'reader' => ['reader', 1],
    'owner' => ['owner', 1],
]);

test('events of a public calendar load without membership', function (): void {
    $this->calendar->update(['is_public' => true]);

    $events = Livewire::test(CalendarComponent::class)
        ->instance()
        ->getEvents($this->period, ['id' => $this->calendar->getKey(), 'permission' => 'reader']);

    expect($events)->toHaveCount(1);
});

test('virtual events load only for listed model types', function (): void {
    $component = Livewire::test(CalendarComponent::class)->instance();

    expect(
        $component->getEvents($this->period, ['modelType' => 'user', 'isVirtual' => true])
    )->toBe([]);

    $component->getEvents($this->period, ['modelType' => morph_alias(Task::class), 'isVirtual' => true]);
});

test('an event changes only with write access', function (?string $permission, bool $deleted): void {
    if ($permission) {
        $this->user->calendars()->attach($this->calendar->getKey(), ['permission' => $permission]);
    }

    Livewire::test(CalendarEventComponent::class)
        ->set('event.id', $this->calendarEvent->getKey())
        ->set('event.calendar_id', $this->calendar->getKey())
        ->set('event.confirm_option', 'all')
        ->call('delete');

    expect(CalendarEvent::query()->whereKey($this->calendarEvent->getKey())->exists())->toBe(! $deleted);
})->with([
    'no membership' => [null, false],
    'reader' => ['reader', false],
    'editor' => ['editor', true],
    'owner' => ['owner', true],
]);

test('an event can not be moved out of a calendar without write access', function (): void {
    $ownCalendar = Calendar::factory()->create();
    $this->user->calendars()->attach($ownCalendar->getKey(), ['permission' => 'owner']);

    Livewire::test(CalendarEventComponent::class)
        ->set('event.id', $this->calendarEvent->getKey())
        ->set('event.calendar_id', $ownCalendar->getKey())
        ->set('event.confirm_option', 'all')
        ->call('delete');

    expect(CalendarEvent::query()->whereKey($this->calendarEvent->getKey())->exists())->toBeTrue();
});

test('a calendar changes only with write access', function (?string $permission, bool $deleted): void {
    if ($permission) {
        $this->user->calendars()->attach($this->calendar->getKey(), ['permission' => $permission]);
    }

    Livewire::test(CalendarComponent::class)
        ->call('editCalendar', $this->calendar->getKey())
        ->call('deleteCalendar');

    expect(Calendar::query()->whereKey($this->calendar->getKey())->exists())->toBe(! $deleted);
})->with([
    'no membership' => [null, false],
    'reader' => ['reader', false],
    'owner' => ['owner', true],
]);
