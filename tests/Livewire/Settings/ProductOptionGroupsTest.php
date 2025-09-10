<?php

use FluxErp\Livewire\Settings\ProductOptionGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductOptionGroups::class)
        ->assertOk();
});
