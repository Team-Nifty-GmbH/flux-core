<?php

use FluxErp\Livewire\Widgets\RevenueBySalesRepresentative;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RevenueBySalesRepresentative::class)
        ->assertOk();
});
