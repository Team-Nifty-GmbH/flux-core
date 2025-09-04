<?php

use FluxErp\Livewire\DataTables\LanguageList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LanguageList::class)
        ->assertOk();
});
