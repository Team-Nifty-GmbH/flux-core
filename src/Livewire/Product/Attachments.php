<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\FolderTree;
use FluxErp\Models\Product;

class Attachments extends FolderTree
{
    public bool $card = true;

    public ?string $modelType = Product::class;

    public ?string $title = 'Product Attachments';

    public function getTree(): array
    {
        $collections = parent::getTree();

        return array_filter($collections, function ($collection) {
            return data_get($collection, 'collection_name') !== 'images';
        });
    }
}
