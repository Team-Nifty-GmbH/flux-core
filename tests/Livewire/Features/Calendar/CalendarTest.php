<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Features\Calendar\Calendar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Calendar::class)
        ->assertStatus(200);
});
