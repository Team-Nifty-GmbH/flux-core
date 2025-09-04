<?php

use FluxErp\Livewire\Settings\FailedJobs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(FailedJobs::class)
        ->assertOk();
});
