<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Product;

class Attachments extends FolderTree
{
    protected string $modelType = Product::class;

    public function getTree(array $exclude = []): array
    {
        return parent::getTree(array_merge($exclude, ['images']));
    }
}
