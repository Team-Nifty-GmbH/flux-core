<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\Lead;
use Livewire\Attributes\Locked;

class Comments extends BaseComments
{
    #[Locked]
    public string $modelType = Lead::class;
}
