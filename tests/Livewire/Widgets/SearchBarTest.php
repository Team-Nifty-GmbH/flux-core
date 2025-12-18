<?php

use FluxErp\Livewire\Widgets\SearchBar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SearchBar::class)
        ->assertOk();
});
