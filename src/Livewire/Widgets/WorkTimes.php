<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\WorkTime;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WorkTimes extends MyWorkTimes
{
    public ?int $userId = null;

    public static function getCategory(): ?string
    {
        return 'Time Tracking';
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.work-times');
    }

    public function updatedUserId(): void
    {
        $this->skipRender();
        $this->calculateByTimeFrame();
    }

    protected function liveUpdates(): array
    {
        return [
            $this->modelChannel(WorkTime::class) => [
                WorkTime::class => ['created', 'updated', 'deleted'],
            ],
        ];
    }
}
