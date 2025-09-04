<?php

use FluxErp\Livewire\Contact\Accounting\DiscountGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DiscountGroups::class)
        ->assertOk();
});
