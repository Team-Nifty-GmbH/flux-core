<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Ticket;

class Media extends FolderTree
{
    protected string $modelType = Ticket::class;
}
