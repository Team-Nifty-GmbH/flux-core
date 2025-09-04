<?php

use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->tickets = Ticket::factory()->count(3)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
    ]);

    $this->comments = Comment::factory()->count(3)->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->tickets[0]->id,
        'comment' => 'User Comment from a Test!',
    ]);

    $this->eventSubscriptions = EventSubscription::factory()->count(3)->create([
        'channel' => $this->user->broadcastChannel(),
        'event' => '*',
        'subscribable_type' => $this->user->getMorphClass(),
        'subscribable_id' => $this->user->id,
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.events.get'),
        'index' => Permission::findOrCreate('api.event-subscriptions.get'),
        'getUserSubscriptions' => Permission::findOrCreate('api.event-subscriptions.user.get'),
        'create' => Permission::findOrCreate('api.event-subscriptions.post'),
        'update' => Permission::findOrCreate('api.event-subscriptions.put'),
        'delete' => Permission::findOrCreate('api.event-subscriptions.{id}.delete'),
    ];
});

test('create event subscription', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
    ]);
    $comment = Comment::factory()->create([
        'model_type' => $ticket->getMorphClass(),
        'model_id' => $ticket->getKey(),
        'comment' => 'User Comment from a Test!',
    ]);

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $subscription = [
        'channel' => $comment->broadcastChannel(),
        'event' => '*',
        'subscribable_type' => $this->user->getMorphClass(),
        'subscribable_id' => $this->user->id,
        'is_broadcast' => true,
        'is_notifiable' => false,
    ];

    $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
    $response->assertCreated();

    $eventSubscription = json_decode($response->getContent())->data;
    $dbEventSubscription = EventSubscription::query()
        ->whereKey($eventSubscription->id)
        ->first();

    expect($dbEventSubscription->subscribable_type)->toEqual($subscription['subscribable_type']);
    expect($dbEventSubscription->subscribable_id)->toEqual($subscription['subscribable_id']);
    expect($dbEventSubscription->channel)->toEqual($subscription['channel']);
    expect($dbEventSubscription->is_broadcast)->toEqual($subscription['is_broadcast']);
    expect($dbEventSubscription->is_notifiable)->toEqual($subscription['is_notifiable']);
});

test('create event subscription already subscribed', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $subscription = [
        'channel' => $this->user->broadcastChannel(),
        'event' => '*',
        'subscribable_type' => $this->user->getMorphClass(),
        'subscribable_id' => $this->user->id,
        'is_broadcast' => true,
        'is_notifiable' => false,
    ];

    $eventSubscription = new EventSubscription($subscription);
    $eventSubscription->save();

    $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
    $response->assertUnprocessable();
});

test('create event subscription validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $subscription = [
        'channel' => Str::random(),
        'event' => '*',
        'subscribable_type' => $this->user->getMorphClass(),
        'subscribable_id' => $this->user->id,
    ];

    $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
    $response->assertUnprocessable();
});

test('delete event subscription', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/event-subscriptions/' . $this->eventSubscriptions[0]->id);

    $response->assertNoContent();

    expect(EventSubscription::query()->whereKey($this->eventSubscriptions[0]->id)->exists())->toBeFalse();
});

test('delete event subscription event subscription not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/event-subscriptions/' . ++$this->eventSubscriptions[2]->id);

    $response->assertNotFound();
});

test('get events', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/events');
    $response->assertOk();

    expect(in_array(
        'eloquent.created: ' . Comment::class,
        json_decode($response->getContent())->data
    ))->toBeTrue();
});

test('get user subscriptions', function (): void {
    $this->user->givePermissionTo($this->permissions['getUserSubscriptions']);
    $this->user->eventSubscriptions()->whereKeyNot($this->eventSubscriptions->pluck('id'))->delete();
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/event-subscriptions/user');
    $response->assertOk();

    $dbUserSubscriptions = json_decode($response->getContent())->data;
    expect($dbUserSubscriptions)->not->toBeEmpty();
    expect($dbUserSubscriptions[0]->id)->toEqual($this->eventSubscriptions[0]->id);
    expect($dbUserSubscriptions[0]->channel)->toEqual($this->eventSubscriptions[0]->channel);
    expect($dbUserSubscriptions[0]->event)->toEqual($this->eventSubscriptions[0]->event);
    expect($dbUserSubscriptions[0]->subscribable_type)->toEqual($this->eventSubscriptions[0]->subscribable_type);
    expect($dbUserSubscriptions[0]->subscribable_id)->toEqual($this->eventSubscriptions[0]->subscribable_id);
    expect($dbUserSubscriptions[0]->is_broadcast)->toEqual($this->eventSubscriptions[0]->is_broadcast);
    expect($dbUserSubscriptions[0]->is_notifiable)->toEqual($this->eventSubscriptions[0]->is_notifiable);
});

test('update event subscription', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $subscription = [
        'id' => $this->eventSubscriptions[0]->id,
        'channel' => $this->comments[2]->broadcastChannel(),
        'subscribable_type' => $this->user->getMorphClass(),
        'subscribable_id' => $this->user->id,
        'is_broadcast' => true,
        'is_notifiable' => false,
    ];

    $response = $this->actingAs($this->user)->put('/api/event-subscriptions', $subscription);
    $response->assertOk();

    $eventSubscription = json_decode($response->getContent())->data;
    $dbEventSubscription = EventSubscription::query()
        ->whereKey($eventSubscription->id)
        ->first();

    expect($dbEventSubscription->id)->toEqual($subscription['id']);
    expect($dbEventSubscription->channel)->toEqual($subscription['channel']);
    expect($dbEventSubscription->subscribable_type)->toEqual($subscription['subscribable_type']);
    expect($dbEventSubscription->subscribable_id)->toEqual($subscription['subscribable_id']);
    expect($dbEventSubscription->is_broadcast)->toEqual($subscription['is_broadcast']);
    expect($dbEventSubscription->is_notifiable)->toEqual($subscription['is_notifiable']);
});

test('update event subscription event subscription not found', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $subscription = [
        'id' => ++$this->eventSubscriptions[2]->id,
        'subscribable_type' => $this->user->getMorphClass(),
        'subscribable_id' => $this->user->id,
        'is_broadcast' => true,
        'is_notifiable' => false,
    ];

    $response = $this->actingAs($this->user)->put('/api/event-subscriptions', $subscription);
    $response->assertUnprocessable();
});

test('update event subscription validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $subscription = [
        'channel' => class_basename('eloquent.created: FluxErp\Models\Comment'),
        'subscribable_type' => $this->user->getMorphClass(),
        'subscribable_id' => $this->user->id,
        'is_broadcast' => true,
        'is_notifiable' => false,
    ];

    $response = $this->actingAs($this->user)->put('/api/event-subscriptions', $subscription);
    $response->assertUnprocessable();
});
