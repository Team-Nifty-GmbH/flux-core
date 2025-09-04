<?php

use FluxErp\Livewire\Settings\AdditionalColumns;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AdditionalColumns::class)
        ->assertStatus(200);
});
