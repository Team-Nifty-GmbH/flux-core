<?php

use FluxErp\Livewire\Portal\Shop\WatchlistCard;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WatchlistCard::class)
        ->assertOk();
});
