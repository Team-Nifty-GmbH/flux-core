<?php

use FluxErp\Livewire\Widgets\RecentActivities;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecentActivities::class)
        ->assertOk();
});
