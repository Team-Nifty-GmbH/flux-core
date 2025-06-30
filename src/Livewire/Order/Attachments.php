<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Order;

class Attachments extends FolderTree
{
    protected string $modelType = Order::class;
}
