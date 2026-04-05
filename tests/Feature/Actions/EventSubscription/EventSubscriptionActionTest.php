<?php

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\EventSubscription\DeleteEventSubscription;
use FluxErp\Actions\EventSubscription\UpdateEventSubscription;

test('create event subscription', function (): void {
    $sub = CreateEventSubscription::make([
        'channel' => 'test-channel',
        'event' => 'test-event',
        'subscribable_type' => morph_alias($this->user::class),
        'subscribable_id' => $this->user->getKey(),
        'is_broadcast' => true,
        'is_notifiable' => false,
    ])->validate()->execute();

    expect($sub)->channel->toBe('test-channel');
});

test('create event subscription requires channel event', function (): void {
    CreateEventSubscription::assertValidationErrors([], ['channel', 'event']);
});

test('update event subscription', function (): void {
    $sub = CreateEventSubscription::make([
        'channel' => 'ch1',
        'event' => 'ev1',
        'subscribable_type' => morph_alias($this->user::class),
        'subscribable_id' => $this->user->getKey(),
        'is_broadcast' => true,
        'is_notifiable' => false,
    ])->validate()->execute();

    $updated = UpdateEventSubscription::make([
        'id' => $sub->getKey(),
        'is_broadcast' => true,
        'is_notifiable' => true,
    ])->validate()->execute();

    expect($updated->is_notifiable)->toBeTruthy();
});

test('delete event subscription', function (): void {
    $sub = CreateEventSubscription::make([
        'channel' => 'ch2',
        'event' => 'ev2',
        'subscribable_type' => morph_alias($this->user::class),
        'subscribable_id' => $this->user->getKey(),
        'is_broadcast' => true,
        'is_notifiable' => false,
    ])->validate()->execute();

    expect(DeleteEventSubscription::make(['id' => $sub->getKey()])
        ->validate()->execute())->toBeTrue();
});
