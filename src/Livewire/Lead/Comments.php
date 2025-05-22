<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;

class Comments extends BaseComments
{
    public string $modelType = \FluxErp\Models\Lead::class;
}
