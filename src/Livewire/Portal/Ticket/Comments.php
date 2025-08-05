<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Ticket;

class Comments extends BaseComments
{
    public bool $isPublic = false;

    protected string $modelType = Ticket::class;
}
