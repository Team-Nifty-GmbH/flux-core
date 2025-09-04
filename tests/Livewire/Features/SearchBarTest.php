<?php

use FluxErp\Livewire\Features\SearchBar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SearchBar::class)
        ->assertStatus(200);
});
