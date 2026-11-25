<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Product;

class FolderTreeTestClass extends FolderTree
{
    protected string $modelType = Product::class;
}
