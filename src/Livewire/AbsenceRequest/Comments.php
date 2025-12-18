<?php

namespace FluxErp\Livewire\AbsenceRequest;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\AbsenceRequest;

class Comments extends BaseComments
{
    protected string $modelType = AbsenceRequest::class;
}
