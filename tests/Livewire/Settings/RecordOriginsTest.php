<?php

use FluxErp\Livewire\Settings\RecordOrigins;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecordOrigins::class)
        ->assertOk();
});
