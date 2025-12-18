<?php

use FluxErp\Livewire\DataTables\TokenList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TokenList::class)
        ->assertOk();
});
