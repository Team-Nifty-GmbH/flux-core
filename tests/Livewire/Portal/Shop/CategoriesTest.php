<?php

use FluxErp\Livewire\Portal\Shop\Categories;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Categories::class)
        ->assertOk();
});
