<?php

use FluxErp\Models\Address;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Lead;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\Token;
use FluxErp\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->publicCalendar = Calendar::factory()->create(['is_public' => true]);
    $this->sharedCalendar = Calendar::factory()->create(['is_public' => false]);
    $this->otherCalendar = Calendar::factory()->create(['is_public' => false]);
    $this->user->calendars()->attach($this->sharedCalendar, ['permission' => 'reader']);

    $this->publicEvent = CalendarEvent::factory()->create(['calendar_id' => $this->publicCalendar->getKey()]);
    $this->sharedEvent = CalendarEvent::factory()->create(['calendar_id' => $this->sharedCalendar->getKey()]);
    $this->otherEvent = CalendarEvent::factory()->create(['calendar_id' => $this->otherCalendar->getKey()]);

    $this->user->givePermissionTo([
        Permission::findOrCreate('api.calendars.get'),
        Permission::findOrCreate('api.calendars.{id}.get'),
        Permission::findOrCreate('api.calendar-events.get'),
        Permission::findOrCreate('api.calendar-events.{id}.get'),
    ]);
    Sanctum::actingAs($this->user, ['user']);
});

test('index lists public calendars and calendars of the user', function (): void {
    $ids = collect($this->getJson('/api/calendars')->assertOk()->json('data.data'))->pluck('id');

    expect($ids)->toContain($this->publicCalendar->getKey());
    expect($ids)->toContain($this->sharedCalendar->getKey());
    expect($ids)->not->toContain($this->otherCalendar->getKey());
});

test('show returns public calendars and calendars of the user', function (): void {
    $this->getJson('/api/calendars/' . $this->publicCalendar->getKey())->assertOk();
    $this->getJson('/api/calendars/' . $this->sharedCalendar->getKey())->assertOk();
    $this->getJson('/api/calendars/' . $this->otherCalendar->getKey())->assertNotFound();
});

test('index lists events of public calendars and calendars of the user', function (): void {
    $ids = collect($this->getJson('/api/calendar-events')->assertOk()->json('data.data'))->pluck('id');

    expect($ids)->toContain($this->publicEvent->getKey());
    expect($ids)->toContain($this->sharedEvent->getKey());
    expect($ids)->not->toContain($this->otherEvent->getKey());
});

test('show returns events of public calendars and calendars of the user', function (): void {
    $this->getJson('/api/calendar-events/' . $this->publicEvent->getKey())->assertOk();
    $this->getJson('/api/calendar-events/' . $this->sharedEvent->getKey())->assertOk();
    $this->getJson('/api/calendar-events/' . $this->otherEvent->getKey())->assertNotFound();
});

test('membership of another model type with the same key does not count', function (): void {
    $this->otherCalendar->calendarables()->create([
        'calendarable_type' => morph_alias(Address::class),
        'calendarable_id' => $this->user->getKey(),
        'permission' => 'reader',
    ]);

    $this->getJson('/api/calendars/' . $this->otherCalendar->getKey())->assertNotFound();
});

test('super admin reads every calendar and event', function (): void {
    $this->user->assignRole(Role::findOrCreate('Super Admin'));

    $this->getJson('/api/calendars/' . $this->otherCalendar->getKey())->assertOk();
    $this->getJson('/api/calendar-events/' . $this->otherEvent->getKey())->assertOk();
});

test('token reads every calendar', function (): void {
    $token = Token::query()->create(['name' => 'integration']);
    $token->givePermissionTo(Permission::findOrCreate('api.calendars.{id}.get', 'token'));

    auth()->forgetGuards();

    $this->withToken($token->createToken('integration', ['interface'])->plainTextToken)
        ->getJson('/api/calendars/' . $this->otherCalendar->getKey())
        ->assertOk();
});

test('included calendars of another user are limited to readable ones', function (): void {
    $user = User::factory()->create(['language_id' => $this->user->language_id]);
    $user->calendars()->attach([$this->publicCalendar->getKey(), $this->otherCalendar->getKey()]);

    $this->user->givePermissionTo([
        Permission::findOrCreate('api.users.get'),
        Permission::findOrCreate('api.users.{id}.get'),
    ]);

    $showIds = collect(
        $this->getJson('/api/users/' . $user->getKey() . '?include=calendars')
            ->assertOk()
            ->json('data.calendars')
    )
        ->pluck('id');
    $indexIds = collect(
        collect($this->getJson('/api/users?include=calendars')->assertOk()->json('data.data'))
            ->firstWhere('id', $user->getKey())['calendars']
    )
        ->pluck('id');

    expect($showIds->all())->toEqual([$this->publicCalendar->getKey()]);
    expect($indexIds->all())->toEqual([$this->publicCalendar->getKey()]);
});

test('calendar events of a lead are limited to readable calendars', function (): void {
    $lead = Lead::factory()->create();
    $this->publicEvent->update(['model_type' => morph_alias(Lead::class), 'model_id' => $lead->getKey()]);
    $this->otherEvent->update(['model_type' => morph_alias(Lead::class), 'model_id' => $lead->getKey()]);

    expect($lead->calendarEvents()->pluck('id')->all())->toEqual([$this->publicEvent->getKey()]);
});

test('calendars of the user stay complete through the relation', function (): void {
    $this->user->calendars()->attach($this->otherCalendar, ['permission' => 'owner']);

    expect($this->user->calendars()->pluck('calendars.id')->sort()->values()->all())
        ->toEqual(
            collect([$this->sharedCalendar->getKey(), $this->otherCalendar->getKey()])
                ->sort()
                ->values()
                ->all()
        );
});
