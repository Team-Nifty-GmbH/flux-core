<?php

use FluxErp\Livewire\Portal\Order\ProductMedia;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductMedia::class)
        ->assertOk();
});
