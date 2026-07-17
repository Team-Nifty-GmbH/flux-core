<?php

use FluxErp\Models\OrderType;
use Spatie\Activitylog\Models\Activity;

test('logs old and new attribute changes on update', function (): void {
    $orderType = OrderType::factory()->create(['name' => 'Original Name', 'is_active' => true]);

    $orderType->update(['name' => 'Updated Name']);

    $activity = Activity::query()
        ->where('subject_type', morph_alias(OrderType::class))
        ->where('subject_id', $orderType->getKey())
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->attribute_changes->toArray())->toHaveKeys(['old', 'attributes'])
        ->and($activity->attribute_changes['attributes'])->toHaveKey('name', 'Updated Name')
        ->and($activity->attribute_changes['old'])->toHaveKey('name', 'Original Name');
});
