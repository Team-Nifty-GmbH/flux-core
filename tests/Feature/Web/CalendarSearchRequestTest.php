<?php

use FluxErp\Models\Calendar;

beforeEach(function (): void {
    $this->calendar = Calendar::factory()->create(['is_group' => false]);
    $this->user->calendars()->attach($this->calendar, ['permission' => 'owner']);
});

test('only the supported parameters reach the query', function (string $parameter, mixed $value): void {
    $response = $this->post(route('calendar-search'), [$parameter => $value]);

    $response->assertOk();

    expect(collect($response->json())->pluck('id')->all())->toBe([$this->calendar->getKey()]);
})->with([
    'with' => ['with', ['doesNotExist']],
    'whereHas' => ['whereHas', 'doesNotExist'],
    'whereDoesntHave' => ['whereDoesntHave', 'doesNotExist'],
    'whereIn' => ['whereIn', [['id', [-1], 'nope']]],
    'whereNotIn' => ['whereNotIn', [['id', [-1], 'nope']]],
]);

test('supported parameters keep working', function (): void {
    $this->post(route('calendar-search'), ['where' => [['is_group', '=', true]]])
        ->assertOk()
        ->assertJsonCount(0);

    $this->post(route('calendar-search'), ['where' => [['is_group', '=', false]]])
        ->assertOk()
        ->assertJsonFragment(['id' => $this->calendar->getKey()]);
});
