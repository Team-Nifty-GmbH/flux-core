<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Lead;

class Comments extends BaseComments
{
    protected string $modelType = Lead::class;
}
