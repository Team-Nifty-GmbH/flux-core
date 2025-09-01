<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Features\SearchBar;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SearchBar::class)
        ->assertStatus(200);
});
