<?php

use FluxErp\Actions\ResourceBooking\CreateResourceBooking;
use FluxErp\Actions\ResourceBooking\DeleteResourceBooking;
use FluxErp\Actions\ResourceBooking\UpdateResourceBooking;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use Illuminate\Validation\ValidationException;

test('resource booking factory creates a booking', function (): void {
    $resource = Resource::factory()->create();

    $booking = ResourceBooking::factory()->create([
        'resource_id' => $resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 17:00:00',
    ]);

    expect($booking)->toBeInstanceOf(ResourceBooking::class)
        ->and($booking->resource->getKey())->toBe($resource->getKey());
});

beforeEach(function (): void {
    $this->resource = Resource::factory()->create(['allow_overbooking' => false]);
});

test('create booking', function (): void {
    $booking = CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ])->validate()->execute();

    expect($booking)->toBeInstanceOf(ResourceBooking::class);
});

test('create booking requires resource_id start end', function (): void {
    CreateResourceBooking::assertValidationErrors([], ['resource_id', 'start', 'end']);
});

test('overlapping booking is rejected', function (): void {
    CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ])->validate()->execute();

    expect(fn () => CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 10:00:00',
        'end' => '2026-07-01 12:00:00',
    ])->validate())->toThrow(ValidationException::class);
});

test('overlapping booking allowed when overbooking enabled', function (): void {
    $this->resource->update(['allow_overbooking' => true]);

    CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ])->validate()->execute();

    $second = CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 10:00:00',
        'end' => '2026-07-01 12:00:00',
    ])->validate()->execute();

    expect($second)->toBeInstanceOf(ResourceBooking::class);
});

test('update booking does not conflict with itself', function (): void {
    $booking = CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ])->validate()->execute();

    $updated = UpdateResourceBooking::make([
        'id' => $booking->getKey(),
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 09:30:00',
        'end' => '2026-07-01 11:30:00',
    ])->validate()->execute();

    expect($updated->start->format('H:i'))->toBe('09:30');
});

test('delete booking soft deletes', function (): void {
    $booking = ResourceBooking::factory()->create(['resource_id' => $this->resource->getKey()]);

    DeleteResourceBooking::make(['id' => $booking->getKey()])->validate()->execute();

    expect(ResourceBooking::query()->whereKey($booking->getKey())->exists())->toBeFalse()
        ->and(ResourceBooking::withTrashed()->whereKey($booking->getKey())->exists())->toBeTrue();
});

test('update booking onto another bookings slot is rejected', function (): void {
    CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ])->validate()->execute();

    $second = CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 12:00:00',
        'end' => '2026-07-01 14:00:00',
    ])->validate()->execute();

    expect(fn () => UpdateResourceBooking::make([
        'id' => $second->getKey(),
        'start' => '2026-07-01 10:00:00',
        'end' => '2026-07-01 11:00:00',
    ])->validate())->toThrow(ValidationException::class);
});

test('partial update with only start is checked against existing end', function (): void {
    CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ])->validate()->execute();

    $second = CreateResourceBooking::make([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 12:00:00',
        'end' => '2026-07-01 14:00:00',
    ])->validate()->execute();

    expect(fn () => UpdateResourceBooking::make([
        'id' => $second->getKey(),
        'start' => '2026-07-01 10:00:00',
    ])->validate())->toThrow(ValidationException::class);
});
