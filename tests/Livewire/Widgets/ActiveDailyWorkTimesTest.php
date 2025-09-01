<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\ActiveDailyWorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActiveDailyWorkTimes::class)
        ->assertStatus(200);
});
