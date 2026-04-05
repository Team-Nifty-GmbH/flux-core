<?php

use FluxErp\Actions\Calendar\CreateCalendar;
use FluxErp\Actions\Calendar\DeleteCalendar;
use FluxErp\Actions\Calendar\UpdateCalendar;
use FluxErp\Models\Calendar;

test('create calendar', function (): void {
    $calendar = CreateCalendar::make([
        'name' => 'Team Calendar',
        'user_id' => $this->user->getKey(),
    ])->validate()->execute();

    expect($calendar)->toBeInstanceOf(Calendar::class)
        ->name->toBe('Team Calendar');
});

test('create calendar requires name', function (): void {
    CreateCalendar::assertValidationErrors([], 'name');
});

test('update calendar', function (): void {
    $calendar = Calendar::factory()->create();

    $updated = UpdateCalendar::make([
        'id' => $calendar->getKey(),
        'name' => 'Personal Calendar',
    ])->validate()->execute();

    expect($updated->name)->toBe('Personal Calendar');
});

test('delete calendar', function (): void {
    $calendar = Calendar::factory()->create();

    expect(DeleteCalendar::make(['id' => $calendar->getKey()])
        ->validate()->execute())->toBeTrue();
});
