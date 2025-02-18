<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Ticket;

class Communication extends BaseCommunication
{
    protected ?string $modelType = Ticket::class;
}
