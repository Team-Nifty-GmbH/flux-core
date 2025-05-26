<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Livewire\Support\Activities as BaseActivities;
use FluxErp\Models\Ticket;

class Activities extends BaseActivities
{
    protected string $modelType = Ticket::class;
}
