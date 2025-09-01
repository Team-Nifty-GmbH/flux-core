<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\WorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimes::class)
        ->assertStatus(200);
});
