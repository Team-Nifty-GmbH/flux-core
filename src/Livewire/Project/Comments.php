<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Models\Project;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    protected string $modelType = Project::class;
}
