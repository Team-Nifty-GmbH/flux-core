<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Lead;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;

class UserLeadWonLostRatio extends MyLeadWonLostRatio
{
    public bool $showDonutOptions = false;

    public static function getCategory(): ?string
    {
        return 'Leads';
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.user-lead-won-lost-ratio');
    }

    #[Renderless]
    public function updatedUserId(): void
    {
        $this->calculateByTimeFrame();
    }

    protected function liveUpdates(): array
    {
        return [
            $this->modelChannel(Lead::class) => [
                Lead::class => ['created', 'updated', 'deleted'],
            ],
        ];
    }
}
