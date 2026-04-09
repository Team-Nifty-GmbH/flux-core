<?php

use FluxErp\Livewire\Settings\ProductOptionGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductOptionGroups::class)
        ->assertOk();
});

test('open new modal', function (): void {
    Livewire::test(ProductOptionGroups::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors();
});
