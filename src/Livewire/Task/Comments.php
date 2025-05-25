<?php

namespace FluxErp\Livewire\Task;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\Task;
use Livewire\Attributes\Locked;

class Comments extends BaseComments
{
    #[Locked]
    public string $modelType = Task::class;
}
