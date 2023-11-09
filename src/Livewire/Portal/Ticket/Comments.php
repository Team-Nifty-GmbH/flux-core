<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;

class Comments extends BaseComments
{
    public string $modelType = \FluxErp\Models\Ticket::class;

    public bool $isPublic = false;
}
