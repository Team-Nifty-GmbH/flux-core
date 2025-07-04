<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Livewire\Support\Dashboard as BaseDashboard;
use Livewire\Attributes\Modelable;

class Dashboard extends BaseDashboard
{
    #[Modelable]
    public ?int $projectId = null;

    public function getWidgetAttributes(): array
    {
        return [
            'projectId' => $this->projectId,
        ];
    }
}
