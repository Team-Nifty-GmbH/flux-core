<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Models\Task;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    protected string $modelType = Task::class;
}
