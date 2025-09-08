<?php

use FluxErp\Livewire\Settings\QueueMonitor;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(QueueMonitor::class)
        ->assertOk();
});
