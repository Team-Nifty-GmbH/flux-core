<?php

namespace FluxErp\Livewire\OrderPosition;

use FluxErp\Livewire\Support\Activities as BaseActivities;
use FluxErp\Models\OrderPosition;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class Activities extends BaseActivities
{
    #[Locked]
    public ?int $modelId = null;

    protected string $modelType = OrderPosition::class;

    #[On('load-order-position-activities')]
    public function loadActivities(?int $orderPositionId): void
    {
        $this->reset('activities', 'page', 'total');

        $this->modelId = $orderPositionId;

        $this->loadPage(page: 1, perPage: $this->perPage);

        $this->renderIsland(name: 'activities');
    }
}
