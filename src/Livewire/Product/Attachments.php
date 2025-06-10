<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Product;

class Attachments extends FolderTree
{
    public bool $card = true;

    public ?string $title = 'Product Attachments';

    protected string $modelType = Product::class;

    public function getTree(): array
    {
        $collections = parent::getTree();

        return array_filter($collections, function ($collection) {
            return data_get($collection, 'collection_name') !== 'images';
        });
    }
}
