<?php

use FluxErp\Livewire\Settings\ProductPropertyGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductPropertyGroups::class)
        ->assertOk();
});

test('open new modal', function (): void {
    Livewire::test(ProductPropertyGroups::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors();
});
