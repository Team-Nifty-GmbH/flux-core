<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use FluxErp\Models\Ticket;
use Livewire\Attributes\Locked;

class Activities extends BaseActivities
{
    #[Locked]
    public ?string $modelType = Ticket::class;
}
