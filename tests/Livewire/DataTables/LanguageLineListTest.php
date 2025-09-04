<?php

use FluxErp\Livewire\DataTables\LanguageLineList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LanguageLineList::class)
        ->assertOk();
});
