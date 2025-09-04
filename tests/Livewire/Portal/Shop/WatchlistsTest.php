<?php

use FluxErp\Livewire\Portal\Shop\Watchlists;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Watchlists::class)
        ->assertOk();
});
