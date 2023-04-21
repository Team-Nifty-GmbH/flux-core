<?php

namespace FluxErp\Tests\Feature;

use Carbon\Carbon;
use FluxErp\Models\Calendar;
use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class CalendarTest extends BaseSetup
{
    use DatabaseTransactions, WithFaker;

    private array $permissions;

    private Collection $calendars;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendars = Calendar::factory()->count(3)->create();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.calendars.{id}.get'),
            'index' => Permission::findOrCreate('api.calendars.get'),
            'create' => Permission::findOrCreate('api.calendars.post'),
            'update' => Permission::findOrCreate('api.calendars.put'),
            'delete' => Permission::findOrCreate('api.calendars.{id}.delete'),
        ];
    }

    public function test_get_calendar()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/calendars/' . $this->calendars[0]->id);
        $response->assertStatus(200);

        $jsonCalendar = json_decode($response->getContent())->data;

        $this->assertNotEmpty($jsonCalendar);
        $this->assertEquals($this->calendars[0]->id, $jsonCalendar->id);
        $this->assertEquals($this->calendars[0]->name, $jsonCalendar->name);
        $this->assertEquals($this->calendars[0]->color, $jsonCalendar->color);
        $this->assertEquals($this->calendars[0]->is_public, $jsonCalendar->is_public);
        $this->assertEquals(Carbon::parse($this->calendars[0]->created_at),
            Carbon::parse($jsonCalendar->created_at));
        $this->assertEquals(Carbon::parse($this->calendars[0]->updated_at),
            Carbon::parse($jsonCalendar->updated_at));
    }

    public function test_get_calendars()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/calendars');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCalendars = collect($json->data->data);

        $this->assertGreaterThanOrEqual(2, count($jsonCalendars));

        foreach ($this->calendars as $calendar) {
            $this->assertTrue($jsonCalendars->contains(function ($jsonCalendar) use ($calendar) {
                return $jsonCalendar->id === $calendar->id &&
                    $jsonCalendar->name === $calendar->name &&
                    $jsonCalendar->color === $calendar->color &&
                    $jsonCalendar->is_public === $calendar->is_public;
            }));
        }
    }

    public function test_create_calendar()
    {
        $calendar = [
            'user_id' => $this->user->id,
            'name' => $this->faker->jobTitle(),
            'module' => 'FluxErp\\Http\\Livewire\\Portal\\Calendars',
            'color' => $this->faker->hexColor(),
            'event_component' => 'FluxErp\\View\\Components\\Logo',
            'is_public' => $this->faker->boolean(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/calendars', $calendar);
        $response->assertStatus(201);

        $responseCalendar = json_decode($response->getContent())->data;
        $dbCalendar = Calendar::query()
            ->whereKey($responseCalendar->id)
            ->first();

        $this->assertNotEmpty($dbCalendar);
        $this->assertEquals($calendar['name'], $dbCalendar['name']);
        $this->assertEquals($calendar['color'], $dbCalendar['color']);
        $this->assertEquals($calendar['is_public'], $dbCalendar['is_public']);
        $this->assertEquals($this->user->id, $dbCalendar->created_by->id);
        $this->assertEquals($this->user->id, $dbCalendar->updated_by->id);
    }

    public function test_create_calendar_validation_fails()
    {
        $calendar = [
            'color' => $this->faker->hexColor(),
            'is_public' => $this->faker->boolean(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/calendars', $calendar);
        $response->assertStatus(422);
    }

    public function test_update_calendar()
    {
        $calendar = [
            'id' => $this->calendars[0]->id,
            'user_id' => $this->user->id,
            'name' => $this->faker->jobTitle(),
            'module' => 'FluxErp\\Http\\Livewire\\Portal\\Calendars',
            'color' => $this->faker->hexColor(),
            'event_component' => 'FluxErp\\View\\Components\\Logo',
            'is_public' => $this->faker->boolean(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/calendars', $calendar);
        $response->assertStatus(200);

        $responseCalendar = json_decode($response->getContent())->data;
        $dbCalendar = Calendar::query()
            ->whereKey($responseCalendar->id)
            ->first();

        $this->assertNotEmpty($dbCalendar);
        $this->assertEquals($calendar['id'], $dbCalendar['id']);
        $this->assertEquals($calendar['name'], $dbCalendar['name']);
        $this->assertEquals($calendar['color'], $dbCalendar['color']);
        $this->assertEquals($calendar['is_public'], $dbCalendar['is_public']);
        $this->assertEquals($this->user->id, $dbCalendar->updated_by->id);
    }

    public function test_update_calendar_calendar_not_found()
    {
        $calendar = [
            'id' => ++$this->calendars[2]->id,
            'name' => $this->faker->jobTitle(),
            'color' => $this->faker->hexColor(),
            'is_public' => $this->faker->boolean(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/calendars', $calendar);
        $response->assertStatus(422);
    }

    public function test_update_calendar_validation_fails()
    {
        $calendar = [
            'id' => $this->calendars[0]->id,
            'color' => $this->faker->hexColor(),
            'is_public' => $this->faker->word(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/calendars', $calendar);
        $response->assertStatus(422);
    }

    public function test_delete_calendar()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/calendars/' . $this->calendars[2]->id);
        $response->assertStatus(204);

        $this->assertFalse(Calendar::query()->whereKey($this->calendars[2]->id)->exists());
    }

    public function test_delete_calendar_calendar_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/calendars/' . ++$this->calendars[2]->id);
        $response->assertStatus(404);
    }
}
