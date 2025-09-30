<?php

use FluxErp\Livewire\DataTables\AttributeTranslationList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AttributeTranslationList::class)
        ->assertOk();
});
