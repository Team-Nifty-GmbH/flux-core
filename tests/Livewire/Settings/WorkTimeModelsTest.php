<?php

use FluxErp\Livewire\Settings\WorkTimeModels;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeModels::class)
        ->assertOk();
});
