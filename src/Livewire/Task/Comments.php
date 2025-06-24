<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Task;

class Comments extends BaseComments
{
    protected string $modelType = Task::class;
}
