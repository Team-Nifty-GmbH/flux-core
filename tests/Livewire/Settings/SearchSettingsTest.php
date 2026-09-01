<?php

use FluxErp\Livewire\Settings\SearchSettings;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SearchSettings::class)
        ->assertOk();
});
