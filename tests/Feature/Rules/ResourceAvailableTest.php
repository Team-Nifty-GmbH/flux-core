<?php

use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use FluxErp\Rules\ResourceAvailable;
use Illuminate\Support\Facades\Validator;

function availabilityFails(int $resourceId, string $start, string $end, ?int $ignoreId = null): bool
{
    $rule = app(ResourceAvailable::class, [
        'resourceId' => $resourceId,
        'start' => $start,
        'end' => $end,
        'ignoreId' => $ignoreId,
    ]);

    return Validator::make(
        ['start' => $start],
        ['start' => [$rule]]
    )->fails();
}

beforeEach(function (): void {
    $this->resource = Resource::factory()->create(['allow_overbooking' => false]);
    ResourceBooking::factory()->create([
        'resource_id' => $this->resource->getKey(),
        'start' => '2026-07-01 10:00:00',
        'end' => '2026-07-01 12:00:00',
    ]);
});

test('overlap is blocked', function (): void {
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 11:00:00', '2026-07-01 13:00:00'))->toBeTrue();
});

test('touching boundary does not conflict', function (): void {
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 12:00:00', '2026-07-01 13:00:00'))->toBeFalse();
});

test('non overlapping is allowed', function (): void {
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 13:00:00', '2026-07-01 14:00:00'))->toBeFalse();
});

test('allow_overbooking bypasses conflict', function (): void {
    $this->resource->update(['allow_overbooking' => true]);
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 11:00:00', '2026-07-01 13:00:00'))->toBeFalse();
});

test('soft deleted booking does not block', function (): void {
    ResourceBooking::query()->where('resource_id', $this->resource->getKey())->delete();
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 11:00:00', '2026-07-01 13:00:00'))->toBeFalse();
});

test('booking can be updated without conflicting with itself', function (): void {
    $booking = ResourceBooking::query()->where('resource_id', $this->resource->getKey())->first();
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 10:30:00', '2026-07-01 11:30:00', $booking->getKey()))->toBeFalse();
});

test('touching boundary before does not conflict', function (): void {
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 09:00:00', '2026-07-01 10:00:00'))->toBeFalse();
});

test('identical interval conflicts', function (): void {
    expect(availabilityFails($this->resource->getKey(), '2026-07-01 10:00:00', '2026-07-01 12:00:00'))->toBeTrue();
});
