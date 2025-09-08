<?php

use FluxErp\Livewire\Support\FolderTree;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(FolderTreeTestClass::class)
        ->assertOk();
});

class FolderTreeTestClass extends FolderTree {}
