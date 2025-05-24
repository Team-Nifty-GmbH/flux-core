<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\Ticket;
use Livewire\Attributes\Locked;

class Comments extends BaseComments
{
    public bool $isPublic = false;

    #[Locked]
    public string $modelType = Ticket::class;
}
