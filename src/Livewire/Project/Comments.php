<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Project;

class Comments extends BaseComments
{
    protected string $modelType = Project::class;
}
