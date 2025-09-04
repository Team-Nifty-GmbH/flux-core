<?php

use FluxErp\Livewire\Settings\PrintJobs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PrintJobs::class)
        ->assertOk();
});
