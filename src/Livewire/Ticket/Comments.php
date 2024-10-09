<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;

class Comments extends BaseComments
{
    public string $modelType = \FluxErp\Models\Ticket::class;
}
