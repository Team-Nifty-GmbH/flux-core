<?php

use FluxErp\Livewire\Settings\SerialNumberRanges;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberRanges::class)
        ->assertOk();
});
