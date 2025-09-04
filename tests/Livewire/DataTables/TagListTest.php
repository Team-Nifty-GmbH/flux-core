<?php

use FluxErp\Livewire\DataTables\TagList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TagList::class)
        ->assertOk();
});
