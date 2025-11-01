<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Employee;

class Attachments extends FolderTree
{
    protected string $modelType = Employee::class;
}
