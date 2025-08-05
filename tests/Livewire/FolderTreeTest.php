<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Order\Order;
use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class FolderTreeTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(FolderTreeTestClass::class)
            ->assertStatus(200);
    }
}

class FolderTreeTestClass extends FolderTree
{
    protected string $modelType = Order::class;
}
