<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    public bool $isPublic = false;

    protected string $modelType = Ticket::class;
}
