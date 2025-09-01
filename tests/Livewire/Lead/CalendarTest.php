<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Lead\Calendar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Calendar::class)
        ->assertStatus(200);
});
