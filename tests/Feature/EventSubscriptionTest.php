<?php

namespace FluxErp\Tests\Feature;

use FluxErp\Events\Print\PdfCreatingEvent;
use FluxErp\Models\Comment;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class EventSubscriptionTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $comments;

    private Collection $eventSubscriptions;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->comments = Comment::factory()->count(3)->create([
            'model_type' => User::class,
            'model_id' => $this->user->id,
            'comment' => 'User Comment from a Test!',
        ]);

        $this->eventSubscriptions = EventSubscription::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'event' => 'eloquent.created: FluxErp\Models\Comment',
            'model_type' => Comment::class,
            'model_id' => $this->comments[0]->id,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.events.get'),
            'index' => Permission::findOrCreate('api.event-subscriptions.get'),
            'getUserSubscriptions' => Permission::findOrCreate('api.event-subscriptions.user'),
            'create' => Permission::findOrCreate('api.event-subscriptions.post'),
            'update' => Permission::findOrCreate('api.event-subscriptions.put'),
            'delete' => Permission::findOrCreate('api.event-subscriptions.{id}.delete'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_events()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/events');
        $response->assertStatus(200);

        $this->assertTrue(in_array('eloquent.created: FluxErp\Models\Comment', json_decode($response->getContent())->data));
    }

    public function test_get_user_subscriptions()
    {
        $this->user->givePermissionTo($this->permissions['getUserSubscriptions']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/event-subscriptions/user');
        $response->assertStatus(200);

        $dbUserSubscriptions = json_decode($response->getContent())->data;
        $this->assertNotEmpty($dbUserSubscriptions);
        $this->assertEquals($this->eventSubscriptions[0]->id, $dbUserSubscriptions[0]->id);
        $this->assertEquals($this->eventSubscriptions[0]->user_id, $dbUserSubscriptions[0]->user_id);
        $this->assertEquals($this->eventSubscriptions[0]->event, $dbUserSubscriptions[0]->event);
        $this->assertEquals($this->eventSubscriptions[0]->model_type, $dbUserSubscriptions[0]->model_type);
        $this->assertEquals($this->eventSubscriptions[0]->model_id, $dbUserSubscriptions[0]->model_id);
    }

    public function test_create_event_subscription()
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'user_id' => $this->user->id,
            'event' => 'eloquent.created: FluxErp\Models\Comment',
            'model_type' => Comment::class,
            'model_id' => $this->comments[2]->id,
            'is_broadcast' => true,
            'is_notifiable' => false,
        ];

        $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
        $response->assertStatus(201);

        $eventSubscription = json_decode($response->getContent())->data;
        $dbEventSubscription = EventSubscription::query()
            ->whereKey($eventSubscription->id)
            ->first();

        $this->assertEquals($subscription['user_id'], $dbEventSubscription->user_id);
        $this->assertEquals(class_basename($subscription['event']), class_basename($dbEventSubscription->event));
        $this->assertEquals(
            class_basename($subscription['model_type']),
            class_basename($dbEventSubscription->model_type)
        );
        $this->assertEquals($subscription['model_id'], $dbEventSubscription->model_id);
        $this->assertEquals($subscription['is_broadcast'], $dbEventSubscription->is_broadcast);
        $this->assertEquals($subscription['is_notifiable'], $dbEventSubscription->is_notifiable);
    }

    public function test_create_event_subscription_validation_fails()
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'user_id' => $this->user->id,
            'event' => 'eloquent.created: FluxErp\Models\Comment',
            'model_type' => Comment::class,
            'model_id' => $this->comments[0]->id,
        ];

        $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_create_event_subscription_event_not_found()
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'user_id' => $this->user->id,
            'event' => 'InvalidEvent',
            'model_type' => class_basename(Comment::class),
            'model_id' => null,
            'is_broadcast' => true,
            'is_notifiable' => false,
        ];

        $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_create_event_subscription_model_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'user_id' => $this->user->id,
            'event' => 'eloquent.created: FluxErp\Models\Comment',
            'model_type' => 'InvalidModelType',
            'model_id' => $this->comments[2]->id,
            'is_broadcast' => true,
            'is_notifiable' => false,
        ];

        $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_create_event_subscription_model_id_not_found()
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'user_id' => $this->user->id,
            'event' => 'eloquent.created: FluxErp\Models\Comment',
            'model_type' => Comment::class,
            'model_id' => ++$this->comments[2]->id,
            'is_broadcast' => true,
            'is_notifiable' => false,
        ];

        $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_create_event_subscription_already_subscribed()
    {
        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'user_id' => $this->user->id,
            'event' => 'eloquent.created: FluxErp\Models\Comment',
            'model_type' => Comment::class,
            'model_id' => $this->comments[0]->id,
            'is_broadcast' => true,
            'is_notifiable' => false,
        ];

        $eventSubscription = new EventSubscription($subscription);
        $eventSubscription->save();

        $response = $this->actingAs($this->user)->post('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_update_event_subscription()
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'id' => $this->eventSubscriptions[0]->id,
            'user_id' => $this->user->id,
            'event' => PdfCreatingEvent::class,
            'model_type' => Comment::class,
            'model_id' => $this->comments[1]->id,
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
        $this->assertEquals($subscription['user_id'], $dbEventSubscription->user_id);
        $this->assertEquals(class_basename($subscription['event']), class_basename($dbEventSubscription->event));
        $this->assertEquals(
            class_basename($subscription['model_type']),
            class_basename($dbEventSubscription->model_type)
        );
        $this->assertEquals($subscription['model_id'], $dbEventSubscription->model_id);
        $this->assertEquals($subscription['is_broadcast'], $dbEventSubscription->is_broadcast);
        $this->assertEquals($subscription['is_notifiable'], $dbEventSubscription->is_notifiable);
    }

    public function test_update_event_subscription_validation_fails()
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'user_id' => $this->user->id,
            'event' => class_basename('eloquent.created: FluxErp\Models\Comment'),
            'model_type' => class_basename(Comment::class),
            'model_id' => $this->comments[1]->id,
            'is_broadcast' => true,
            'is_notifiable' => false,
        ];

        $response = $this->actingAs($this->user)->put('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_update_event_subscription_event_subscription_not_found()
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $subscription = [
            'id' => ++$this->eventSubscriptions[2]->id,
            'user_id' => $this->user->id,
            'event' => class_basename('eloquent.created: FluxErp\Models\Comment'),
            'model_type' => class_basename(Comment::class),
            'model_id' => $this->comments[1]->id,
            'is_broadcast' => true,
            'is_notifiable' => false,
        ];

        $response = $this->actingAs($this->user)->put('/api/event-subscriptions', $subscription);
        $response->assertStatus(422);
    }

    public function test_delete_event_subscription()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/event-subscriptions/' . $this->eventSubscriptions[0]->id);

        $response->assertStatus(204);

        $this->assertFalse(EventSubscription::query()->whereKey($this->eventSubscriptions[0]->id)->exists());
    }

    public function test_delete_event_subscription_event_subscription_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/event-subscriptions/' . ++$this->eventSubscriptions[2]->id);

        $response->assertStatus(404);
    }
}
