<?php

use FluxErp\Livewire\Contact\Accounting\AllDiscounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AllDiscounts::class)
        ->assertStatus(200);
});
