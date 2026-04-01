<?php

use FluxErp\Livewire\HumanResources\AttendanceOverview;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AttendanceOverview::class)
        ->assertOk();
});
