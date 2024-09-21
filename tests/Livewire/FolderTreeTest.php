<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\FolderTree;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class FolderTreeTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(FolderTree::class)
            ->assertStatus(200);
    }
}
