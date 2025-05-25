<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Models\Lead;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    protected string $modelType = Lead::class;
}
