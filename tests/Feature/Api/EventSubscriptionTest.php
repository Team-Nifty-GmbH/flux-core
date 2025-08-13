<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class EventSubscriptionTest extends BaseSetup
{
    private Collection $comments;

    private Collection $eventSubscriptions;

    private array $permissions;

    private Collection $tickets;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tickets = Ticket::factory()->count(3)->create([
            'authenticatable_type' => $this->user->getMorphClass(),
            'authenticatable_id' => $this->user->getKey(),
        ]);

        $this->comments = Comment::factory()->count(3)->create([
            'model_type' => morph_alias(Ticket::class),
            'model_id' => $this->tickets[0]->id,
            'comment' => '<p>
                <span class="mention" data-type="mention" data-id="'
                    . $this->user->getMorphClass() . ':' . $this->user->getKey()
                    . '" data-label="' . $this->user->getLabel()
                    . '" data-mention-suggestion-char="@">@' . $this->user->getLabel() . '
                </span>  Please do something!
            </p>',
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
    }

    public function test_create_event_subscription(): void
    {
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
        $response->assertStatus(201);

        $eventSubscription = json_decode($response->getContent())->data;
        $dbEventSubscription = EventSubscription::query()
            ->whereKey($eventSubscription->id)
            ->first();

        $this->assertEquals($subscription['subscribable_type'], $dbEventSubscription->subscribable_type);
        $this->assertEquals($subscription['subscribable_id'], $dbEventSubscription->subscribable_id);
        $this->assertEquals($subscription['channel'], $dbEventSubscription->channel);
        $this->assertEquals($subscription['is_broadcast'], $dbEventSubscription->is_broadcast);
        $this->assertEquals($subscription['is_notifiable'], $dbEventSubscription->is_notifiable);
    }

    public function test_create_event_subscription_already_subscribed(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_create_event_subscription_validation_fails(): void
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'channel' => Str::random(),
            'event' => '*',
            'subscribable_type' => $this->user->getMorphClass(),
            'subscribable_id' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_delete_event_subscription(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/event-subscriptions/' . $this->eventSubscriptions[0]->id);

        $response->assertStatus(204);

        $this->assertFalse(EventSubscription::query()->whereKey($this->eventSubscriptions[0]->id)->exists());
    }

    public function test_delete_event_subscription_event_subscription_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/event-subscriptions/' . ++$this->eventSubscriptions[2]->id);

        $response->assertStatus(404);
    }

    public function test_get_events(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/events');
        $response->assertStatus(200);

        $this->assertTrue(
            in_array(
                $this->tickets[0]->getMorphClass() . '.' . $this->tickets[0]->getKey(),
                json_decode($response->getContent())->data
            )
        );
    }

    public function test_get_user_subscriptions(): void
    {
        $this->user->givePermissionTo($this->permissions['getUserSubscriptions']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/event-subscriptions/user');
        $response->assertStatus(200);

        $dbUserSubscriptions = json_decode($response->getContent())->data;
        $this->assertNotEmpty($dbUserSubscriptions);
        $this->assertEquals($this->eventSubscriptions[0]->id, $dbUserSubscriptions[0]->id);
        $this->assertEquals($this->eventSubscriptions[0]->channel, $dbUserSubscriptions[0]->channel);
        $this->assertEquals($this->eventSubscriptions[0]->event, $dbUserSubscriptions[0]->event);
        $this->assertEquals(
            $this->eventSubscriptions[0]->subscribable_type,
            $dbUserSubscriptions[0]->subscribable_type
        );
        $this->assertEquals($this->eventSubscriptions[0]->subscribable_id, $dbUserSubscriptions[0]->subscribable_id);
        $this->assertEquals($this->eventSubscriptions[0]->is_broadcast, $dbUserSubscriptions[0]->is_broadcast);
        $this->assertEquals($this->eventSubscriptions[0]->is_notifiable, $dbUserSubscriptions[0]->is_notifiable);
    }

    public function test_update_event_subscription(): void
    {
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
        $response->assertStatus(200);

        $eventSubscription = json_decode($response->getContent())->data;
        $dbEventSubscription = EventSubscription::query()
            ->whereKey($eventSubscription->id)
            ->first();

        $this->assertEquals($subscription['id'], $dbEventSubscription->id);
        $this->assertEquals($subscription['channel'], $dbEventSubscription->channel);
        $this->assertEquals($subscription['subscribable_type'], $dbEventSubscription->subscribable_type);
        $this->assertEquals($subscription['subscribable_id'], $dbEventSubscription->subscribable_id);
        $this->assertEquals($subscription['is_broadcast'], $dbEventSubscription->is_broadcast);
        $this->assertEquals($subscription['is_notifiable'], $dbEventSubscription->is_notifiable);
    }

    public function test_update_event_subscription_event_subscription_not_found(): void
    {
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
        $response->assertStatus(422);
    }

    public function test_update_event_subscription_validation_fails(): void
    {
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
        $response->assertStatus(422);
    }
}
