<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Support\Livewire\Activities as BaseActivities;

class Activities extends BaseActivities
{
    protected string $modelType = Ticket::class;
}
