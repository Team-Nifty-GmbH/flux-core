<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Livewire\Support\Activities as BaseActivities;
use FluxErp\Models\Ticket;

class Activities extends BaseActivities
{
    protected string $modelType = Ticket::class;
}
