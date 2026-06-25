<?php

use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;

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
