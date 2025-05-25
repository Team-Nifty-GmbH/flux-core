<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\Project;
use Livewire\Attributes\Locked;

class Comments extends BaseComments
{
    #[Locked]
    public string $modelType = Project::class;
}
