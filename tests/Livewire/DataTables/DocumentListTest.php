<?php

use FluxErp\Livewire\DataTables\DocumentList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DocumentList::class)
        ->assertOk();
});
