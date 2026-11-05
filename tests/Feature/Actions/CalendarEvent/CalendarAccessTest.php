<?php

use FluxErp\Actions\Calendar\CreateCalendar;
use FluxErp\Actions\Calendar\DeleteCalendar;
use FluxErp\Actions\Calendar\UpdateCalendar;
use FluxErp\Actions\CalendarEvent\CancelCalendarEvent;
use FluxErp\Actions\CalendarEvent\CreateCalendarEvent;
use FluxErp\Actions\CalendarEvent\DeleteCalendarEvent;
use FluxErp\Actions\CalendarEvent\ReactivateCalendarEvent;
use FluxErp\Actions\CalendarEvent\UpdateCalendarEvent;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Token;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->actingAs($this->user, 'web');

    $this->calendar = Calendar::factory()->create();
    $this->calendarEvent = CalendarEvent::factory()->create([
        'calendar_id' => $this->calendar->getKey(),
        'start' => now(),
        'end' => now()->addHour(),
    ]);
    $this->actions = fn (): array => [
        CreateCalendarEvent::make(['calendar_id' => $this->calendar->getKey()]),
        UpdateCalendarEvent::make(['id' => $this->calendarEvent->getKey()]),
        DeleteCalendarEvent::make(['id' => $this->calendarEvent->getKey()]),
        CancelCalendarEvent::make(['id' => $this->calendarEvent->getKey()]),
        ReactivateCalendarEvent::make(['id' => $this->calendarEvent->getKey()]),
        UpdateCalendar::make(['id' => $this->calendar->getKey()]),
        DeleteCalendar::make(['id' => $this->calendar->getKey()]),
        CreateCalendar::make(['parent_id' => $this->calendar->getKey()]),
    ];
});

test('the permission check needs write access to the calendar', function (?string $permission, bool $allowed): void {
    if ($permission) {
        $this->user->calendars()->attach($this->calendar->getKey(), ['permission' => $permission]);
    }

    foreach (($this->actions)() as $action) {
        $passed = true;

        try {
            $action->checkPermission();
        } catch (UnauthorizedException) {
            $passed = false;
        }

        expect($passed)->toBe($allowed, $action::class);
    }
})->with([
    'no membership' => [null, false],
    'reader' => ['reader', false],
    'editor' => ['editor', true],
    'owner' => ['owner', true],
]);

test('an event needs write access to the calendar it moves to', function (): void {
    $this->user->calendars()->attach($this->calendar->getKey(), ['permission' => 'owner']);

    UpdateCalendarEvent::make([
        'id' => $this->calendarEvent->getKey(),
        'calendar_id' => Calendar::factory()->create()->getKey(),
    ])->checkPermission();
})->throws(UnauthorizedException::class);

test('an action without a permission check stays unaffected', function (): void {
    DeleteCalendarEvent::make([
        'id' => $this->calendarEvent->getKey(),
        'confirm_option' => 'all',
    ])
        ->validate()
        ->execute();

    expect(CalendarEvent::query()->whereKey($this->calendarEvent->getKey())->exists())->toBeFalse();
});

test('a token is limited by its own permissions only', function (): void {
    Auth::setUser(Token::query()->create(['name' => 'integration']));

    expect(fn () => Calendar::ensureWritable([$this->calendar->getKey()]))
        ->not->toThrow(UnauthorizedException::class);
});

test('a super admin is not limited by the membership', function (): void {
    $this->user->assignRole(Role::findOrCreate('Super Admin', 'web'));

    foreach (($this->actions)() as $action) {
        expect($action->checkPermission())->toBe($action);
    }
});

test('an unknown calendar is left to the validation', function (): void {
    DeleteCalendar::make(['id' => 0])->checkPermission();

    expect(UpdateCalendarEvent::make(['id' => 0, 'calendar_id' => 0])->checkPermission())
        ->toBeInstanceOf(UpdateCalendarEvent::class);
});

test('a malformed id is left to the validation', function (string $action, array $data): void {
    expect($action::make($data)->checkPermission())
        ->toBeInstanceOf($action);
})
    ->with([
        'calendar id' => [DeleteCalendar::class, ['id' => [1, [2]]]],
        'calendar id and parent id' => [UpdateCalendar::class, ['id' => [1], 'parent_id' => [2]]],
        'parent id' => [UpdateCalendar::class, ['id' => 0, 'parent_id' => [1]]],
        'event id' => [UpdateCalendarEvent::class, ['id' => [[1]]]],
        'event calendar id' => [UpdateCalendarEvent::class, ['id' => 0, 'calendar_id' => [1]]],
    ]);

test('the api answers with forbidden without write access', function (): void {
    Sanctum::actingAs($this->user, ['user']);

    $this->deleteJson('/api/calendar-events/' . $this->calendarEvent->getKey())
        ->assertForbidden();

    expect(CalendarEvent::query()->whereKey($this->calendarEvent->getKey())->exists())->toBeTrue();
});
