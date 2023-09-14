<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\FolderTree;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class FolderTreeTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(FolderTree::class)
            ->assertStatus(200);
    }
}
