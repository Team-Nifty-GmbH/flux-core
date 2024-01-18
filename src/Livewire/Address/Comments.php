<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\Address;
use Livewire\Attributes\Modelable;

class Comments extends BaseComments
{
    public string $modelType = Address::class;

    #[Modelable]
    public int $modelId;

    public bool $isPublic = false;
}
