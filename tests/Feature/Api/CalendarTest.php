<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Models\Calendar;
use FluxErp\Models\Permission;
use Laravel\Sanctum\Sanctum;

uses(Illuminate\Foundation\Testing\WithFaker::class);

beforeEach(function (): void {
    $this->calendars = Calendar::factory()->count(3)->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.calendars.{id}.get'),
        'index' => Permission::findOrCreate('api.calendars.get'),
        'create' => Permission::findOrCreate('api.calendars.post'),
        'update' => Permission::findOrCreate('api.calendars.put'),
        'delete' => Permission::findOrCreate('api.calendars.{id}.delete'),
    ];
});

test('create calendar', function (): void {
    $calendar = [
        'user_id' => $this->user->id,
        'name' => $this->faker->jobTitle(),
        'color' => $this->faker->hexColor(),
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

    expect($dbCalendar)->not->toBeEmpty();
    expect($dbCalendar['name'])->toEqual($calendar['name']);
    expect($dbCalendar['color'])->toEqual($calendar['color']);
    expect($dbCalendar['is_public'])->toEqual($calendar['is_public']);
    expect($this->user->is($dbCalendar->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbCalendar->getUpdatedBy()))->toBeTrue();
});

test('create calendar validation fails', function (): void {
    $calendar = [
        'color' => $this->faker->hexColor(),
        'is_public' => $this->faker->boolean(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/calendars', $calendar);
    $response->assertStatus(422);
});

test('delete calendar', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/calendars/' . $this->calendars[2]->id);
    $response->assertStatus(204);

    expect(Calendar::query()->whereKey($this->calendars[2]->id)->exists())->toBeFalse();
});

test('delete calendar calendar not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/calendars/' . ++$this->calendars[2]->id);
    $response->assertStatus(404);
});

test('get calendar', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/calendars/' . $this->calendars[0]->id);
    $response->assertStatus(200);

    $jsonCalendar = json_decode($response->getContent())->data;

    expect($jsonCalendar)->not->toBeEmpty();
    expect($jsonCalendar->id)->toEqual($this->calendars[0]->id);
    expect($jsonCalendar->name)->toEqual($this->calendars[0]->name);
    expect($jsonCalendar->color)->toEqual($this->calendars[0]->color);
    expect($jsonCalendar->is_public)->toEqual($this->calendars[0]->is_public);
    expect(Carbon::parse($jsonCalendar->created_at))->toEqual(Carbon::parse($this->calendars[0]->created_at));
    expect(Carbon::parse($jsonCalendar->updated_at))->toEqual(Carbon::parse($this->calendars[0]->updated_at));
});

test('get calendars', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/calendars');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonCalendars = collect($json->data->data);

    expect(count($jsonCalendars))->toBeGreaterThanOrEqual(2);

    foreach ($this->calendars as $calendar) {
        expect($jsonCalendars->contains(function ($jsonCalendar) use ($calendar) {
            return $jsonCalendar->id === $calendar->id &&
                $jsonCalendar->name === $calendar->name &&
                $jsonCalendar->color === $calendar->color &&
                $jsonCalendar->is_public === $calendar->is_public;
        }))->toBeTrue();
    }
});

test('update calendar', function (): void {
    $calendar = [
        'id' => $this->calendars[0]->id,
        'user_id' => $this->user->id,
        'name' => $this->faker->jobTitle(),
        'color' => $this->faker->hexColor(),
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

    expect($dbCalendar)->not->toBeEmpty();
    expect($dbCalendar['id'])->toEqual($calendar['id']);
    expect($dbCalendar['name'])->toEqual($calendar['name']);
    expect($dbCalendar['color'])->toEqual($calendar['color']);
    expect($dbCalendar['is_public'])->toEqual($calendar['is_public']);
    expect($this->user->is($dbCalendar->getUpdatedBy()))->toBeTrue();
});

test('update calendar calendar not found', function (): void {
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
});

test('update calendar validation fails', function (): void {
    $calendar = [
        'id' => $this->calendars[0]->id,
        'color' => $this->faker->hexColor(),
        'is_public' => $this->faker->word(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/calendars', $calendar);
    $response->assertStatus(422);
});
