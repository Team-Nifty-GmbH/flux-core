<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Support\Livewire\Activities as BaseActivities;

class Activities extends BaseActivities
{
    protected string $modelType = Ticket::class;
}
