<?php

use FluxErp\Livewire\Settings\ProductPropertyGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductPropertyGroups::class)
        ->assertOk();
});
