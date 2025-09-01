<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Features\Calendar\CalendarEventEdit;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CalendarEventEdit::class)
        ->assertStatus(200);
});
