<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Features\Calendar\CalendarEvent;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CalendarEvent::class)
        ->assertStatus(200);
});
