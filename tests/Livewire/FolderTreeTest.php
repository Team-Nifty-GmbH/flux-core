<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class FolderTreeTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(FolderTree::class)
            ->assertStatus(200);
    }
}
