<?php

use FluxErp\Livewire\Settings\Logs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Logs::class)
        ->assertStatus(200);
});
