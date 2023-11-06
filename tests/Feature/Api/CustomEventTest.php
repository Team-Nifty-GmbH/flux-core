<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\CustomEvent;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class CustomEventTest extends BaseSetup
{
    use DatabaseTransactions;

    private CustomEvent $customEvent;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customEvent = CustomEvent::factory()->create();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.custom-events.{id}.get'),
            'index' => Permission::findOrCreate('api.custom-events.get'),
            'create' => Permission::findOrCreate('api.custom-events.post'),
            'dispatch' => Permission::findOrCreate('api.custom-events.dispatch.post'),
            'update' => Permission::findOrCreate('api.custom-events.put'),
            'delete' => Permission::findOrCreate('api.custom-events.{id}.delete'),
        ];
    }

    public function test_get_custom_event()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/custom-events/' . $this->customEvent->id);
        $response->assertStatus(200);

        $customEvent = json_decode($response->getContent())->data;

        $this->assertNotEmpty($customEvent);
        $this->assertEquals($this->customEvent->id, $customEvent->id);
        $this->assertEquals($this->customEvent->name, $customEvent->name);
        $this->assertEquals($this->customEvent->model_type, $customEvent->model_type);
        $this->assertEquals($this->customEvent->model_id, $customEvent->model_id);
    }

    public function test_get_custom_event_custom_event_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/custom-events/' . ++$this->customEvent->id);
        $response->assertStatus(404);
    }

    public function test_get_custom_events()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/custom-events');
        $response->assertStatus(200);

        $responseCustomEvents = collect(json_decode($response->getContent())->data->data);

        $customEvent = $this->customEvent;
        $this->assertGreaterThanOrEqual(1, $responseCustomEvents->count());
        $this->assertTrue($responseCustomEvents->contains(function ($responseCustomer) use ($customEvent) {
            return $responseCustomer->id === $customEvent->id &&
                $responseCustomer->name === $customEvent->name &&
                $responseCustomer->model_type === $customEvent->model_type &&
                $responseCustomer->model_id === $customEvent->model_id;
        }));
    }

    public function test_create_custom_event()
    {
        $customEvent = [
            'name' => str_replace([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], '', Str::random(32)),
            'model_type' => get_class($this->user),
            'model_id' => $this->user->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/custom-events', $customEvent);
        $response->assertStatus(201);

        $responseCustomEvent = json_decode($response->getContent())->data;
        $dbCustomEvent = CustomEvent::query()
            ->whereKey($responseCustomEvent->id)
            ->first();

        $this->assertNotEmpty($dbCustomEvent);
        $this->assertEquals($customEvent['name'], $dbCustomEvent->name);
        $this->assertEquals($customEvent['model_type'], $dbCustomEvent->model_type);
        $this->assertEquals($customEvent['model_id'], $dbCustomEvent->model_id);
    }

    public function test_create_custom_event_validation_fails()
    {
        $customEvent = [
            'name' => Str::random(32) . rand(),
            'model_type' => get_class($this->user),
            'model_id' => $this->user->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/custom-events', $customEvent);
        $response->assertStatus(422);
    }

    public function test_update_custom_event()
    {
        $customEvent = [
            'id' => $this->customEvent->id,
            'name' => str_replace([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], '', Str::random(32)),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/custom-events', $customEvent);
        $response->assertStatus(200);

        $responseCustomEvent = json_decode($response->getContent())->data;
        $dbCustomEvent = CustomEvent::query()
            ->whereKey($this->customEvent->id)
            ->first();

        $this->assertNotEmpty($dbCustomEvent);
        $this->assertEquals($customEvent['id'], $responseCustomEvent->id);
        $this->assertEquals($customEvent['name'], $dbCustomEvent->name);
        $this->assertEquals($customEvent['name'], $responseCustomEvent->name);
        $this->assertEquals($this->customEvent->model_type, $dbCustomEvent->model_type);
        $this->assertEquals($this->customEvent->model_id, $dbCustomEvent->model_id);
    }

    public function test_update_custom_event_validation_fails()
    {
        $newCustomEvent = CustomEvent::factory()->create();

        $customEvent = [
            'id' => $this->customEvent->id,
            'name' => $newCustomEvent->name,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/custom-events', $customEvent);
        $response->assertStatus(422);
    }

    public function test_delete_custom_event()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/custom-events/' . $this->customEvent->id);
        $response->assertStatus(204);

        $this->assertFalse(CustomEvent::query()->whereKey($this->customEvent->id)->exists());
    }

    public function test_delete_custom_event_custom_event_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/custom-events/' . ++$this->customEvent->id);
        $response->assertStatus(404);
    }

    public function test_dispatch_custom_event()
    {
        Event::fake();

        $customEvent = [
            'event' => $this->customEvent->name,
        ];

        $this->user->givePermissionTo($this->permissions['dispatch']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/custom-events/dispatch', $customEvent);
        $response->assertStatus(200);

        $expectedPayload = $this->customEvent;
        Event::assertDispatched($customEvent['event'], function ($event, $payload) use ($expectedPayload) {
            return $expectedPayload->id === $payload?->id
                && $expectedPayload->name === $payload?->name
                && $expectedPayload->model_type === $payload?->model_type
                && $expectedPayload->model_id === $payload?->model_id;
        });
    }

    public function test_dispatch_custom_event_with_custom_payload()
    {
        Event::fake();

        $customEvent = [
            'event' => $this->customEvent->name,
            'payload' => $this->user->toArray(),
        ];

        $this->user->givePermissionTo($this->permissions['dispatch']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/custom-events/dispatch', $customEvent);
        $response->assertStatus(200);

        Event::assertDispatched($customEvent['event'], function ($event, $payload) use ($customEvent) {
            return $customEvent['payload'] === $payload;
        });
    }

    public function test_dispatch_custom_event_validation_fails()
    {
        Event::fake();

        $customEvent = [
            'event' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['dispatch']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/custom-events/dispatch', $customEvent);
        $response->assertStatus(422);

        Event::assertNotDispatched($customEvent['event']);
    }
}
