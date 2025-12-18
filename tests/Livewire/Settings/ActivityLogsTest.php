<?php

use FluxErp\Livewire\Settings\ActivityLogs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActivityLogs::class)
        ->assertOk();
});
