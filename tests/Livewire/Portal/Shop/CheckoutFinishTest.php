<?php

use FluxErp\Livewire\Portal\Shop\CheckoutFinish;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CheckoutFinish::class)
        ->assertOk();
});
