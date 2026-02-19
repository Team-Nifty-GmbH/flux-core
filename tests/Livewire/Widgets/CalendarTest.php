<?php

use FluxErp\Livewire\Widgets\Calendar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Calendar::class)
        ->assertOk();
});

test('can call get calendars', function (): void {
    Livewire::test(Calendar::class)
        ->call('getCalendars')
        ->assertOk();
});
