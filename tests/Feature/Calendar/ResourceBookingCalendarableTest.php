<?php

use FluxErp\Actions\ResourceBooking\CreateResourceBooking;
use FluxErp\Actions\ResourceBooking\DeleteResourceBooking;
use FluxErp\Actions\ResourceBooking\UpdateResourceBooking;
use FluxErp\Contracts\Calendarable;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use Illuminate\Database\Eloquent\Relations\Relation;

test('ResourceBooking implements Calendarable', function (): void {
    expect(in_array(Calendarable::class, class_implements(ResourceBooking::class)))->toBeTrue();
});

test('ResourceBooking is in morphMap and implements Calendarable', function (): void {
    $morphMap = Relation::morphMap();

    $calendarableClasses = array_filter(
        $morphMap,
        fn ($class) => in_array(Calendarable::class, class_implements($class))
    );

    expect($calendarableClasses)->toHaveKey('resource_booking');
});

test('toCalendar returns expected keys', function (): void {
    $calendar = ResourceBooking::toCalendar();

    expect($calendar)
        ->toHaveKeys(['id', 'modelType', 'name', 'color', 'resourceEditable', 'hasRepeatableEvents', 'isPublic', 'isShared', 'permission', 'group', 'isVirtual'])
        ->and($calendar['modelType'])->toBe('resource_booking')
        ->and($calendar['isVirtual'])->toBeTrue();
});

test('toCalendarEvent returns expected keys for a booking with resource', function (): void {
    $resource = Resource::factory()->create(['name' => 'Meeting Room A']);

    $booking = ResourceBooking::factory()->create([
        'resource_id' => $resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ]);

    $event = $booking->toCalendarEvent();

    expect($event)
        ->toHaveKeys(['id', 'calendar_type', 'title', 'start', 'end', 'description', 'extendedProps', 'allDay', 'is_editable', 'is_public', 'is_repeatable'])
        ->and($event['id'])->toBe($booking->getKey())
        ->and($event['calendar_type'])->toBe('resource_booking')
        ->and($event['title'])->toBe('Meeting Room A')
        ->and($event['start'])->toBe('2026-07-01 09:00:00')
        ->and($event['end'])->toBe('2026-07-01 11:00:00')
        ->and($event['allDay'])->toBeFalse()
        ->and($event['is_editable'])->toBeTrue();
});

test('scopeInTimeframe returns booking inside window', function (): void {
    $resource = Resource::factory()->create();

    $booking = ResourceBooking::factory()->create([
        'resource_id' => $resource->getKey(),
        'start' => '2026-07-10 09:00:00',
        'end' => '2026-07-10 11:00:00',
    ]);

    $results = ResourceBooking::query()
        ->inTimeframe('2026-07-01 00:00:00', '2026-07-31 23:59:59')
        ->get();

    expect($results->contains($booking))->toBeTrue();
});

test('scopeInTimeframe excludes booking fully outside window', function (): void {
    $resource = Resource::factory()->create();

    $outside = ResourceBooking::factory()->create([
        'resource_id' => $resource->getKey(),
        'start' => '2026-08-01 09:00:00',
        'end' => '2026-08-01 11:00:00',
    ]);

    $results = ResourceBooking::query()
        ->inTimeframe('2026-07-01 00:00:00', '2026-07-31 23:59:59')
        ->get();

    expect($results->contains($outside))->toBeFalse();
});

test('fromCalendarEvent with update action returns UpdateResourceBooking', function (): void {
    $action = ResourceBooking::fromCalendarEvent([
        'id' => 1,
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ], 'update');

    expect($action)->toBeInstanceOf(UpdateResourceBooking::class);
});

test('fromCalendarEvent with default action returns UpdateResourceBooking', function (): void {
    $action = ResourceBooking::fromCalendarEvent([
        'id' => 1,
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ]);

    expect($action)->toBeInstanceOf(UpdateResourceBooking::class);
});

test('fromCalendarEvent with delete action returns DeleteResourceBooking', function (): void {
    $action = ResourceBooking::fromCalendarEvent(['id' => 1], 'delete');

    expect($action)->toBeInstanceOf(DeleteResourceBooking::class);
});

test('fromCalendarEvent with create action returns CreateResourceBooking', function (): void {
    $action = ResourceBooking::fromCalendarEvent([
        'resource_id' => 1,
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ], 'create');

    expect($action)->toBeInstanceOf(CreateResourceBooking::class);
});
