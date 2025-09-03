<?php

namespace FluxErp\Tests\Livewire;

uses(\FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Support\FolderTree;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(FolderTreeTestClass::class)
        ->assertStatus(200);
});

class FolderTreeTestClass extends FolderTree {}
