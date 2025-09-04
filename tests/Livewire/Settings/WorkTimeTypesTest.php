<?php

use FluxErp\Livewire\Settings\WorkTimeTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->assertOk();
});
