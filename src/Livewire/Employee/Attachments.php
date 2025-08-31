<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\User;

class Attachments extends FolderTree
{
    protected string $modelType = User::class;
}
