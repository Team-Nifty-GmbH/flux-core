<?php

use FluxErp\Livewire\DataTables\DocumentTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DocumentTypeList::class)
        ->assertOk();
});
