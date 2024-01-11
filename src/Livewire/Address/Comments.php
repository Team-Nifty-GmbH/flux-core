<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use Livewire\Attributes\Modelable;

class Comments extends BaseComments
{
    public string $modelType = \FluxErp\Models\Address::class;

    public bool $isPublic = false;

    #[Modelable]
    public int $modelId;
}
