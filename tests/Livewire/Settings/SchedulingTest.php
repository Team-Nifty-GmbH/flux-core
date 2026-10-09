<?php

use FluxErp\Livewire\Settings\Scheduling;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Scheduling::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Scheduling::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('schedule.id', null)
        ->assertSet('schedule.name', null)
        ->assertSet('schedule.is_active', true);
});

test('renders parameters that do not fit the inputs of the other repeat methods', function (string $method, array $parameters): void {
    Livewire::test(Scheduling::class)
        ->set('schedule.cron.methods.basic', $method)
        ->set('schedule.cron.parameters.basic', $parameters)
        ->assertOk()
        ->assertSet('schedule.cron.parameters.basic', $parameters);
})->with([
    'a day of month above the highest hour' => ['monthlyOn', [31, '15:00', null]],
    'an hour where another method keeps a time' => ['twiceDaily', [1, 13, null]],
]);

test('switching the repeat method clears the parameters of the previous one', function (): void {
    Livewire::test(Scheduling::class)
        ->set('schedule.cron.methods.basic', 'monthlyOn')
        ->set('schedule.cron.parameters.basic', [28, '15:00', null])
        ->set('schedule.cron.methods.basic', 'twiceDaily')
        ->assertOk()
        ->assertSet('schedule.cron.parameters.basic', [null, null, null]);
});

test('renders the time picker without a schedule preview', function (): void {
    $html = Livewire::test(Scheduling::class)
        ->set('schedule.cron.methods.basic', 'dailyAt')
        ->html();

    expect($html)
        ->toContain('tallstackui_formTime(')
        ->not->toContain('<x-')
        ->not->toContain('previewSchedule');
});
