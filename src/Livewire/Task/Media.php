<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Task;

class Media extends FolderTree
{
    protected string $modelType = Task::class;
}
