<?php

use FluxErp\Livewire\Contact\Accounting\Discounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Discounts::class)
        ->assertOk();
});
