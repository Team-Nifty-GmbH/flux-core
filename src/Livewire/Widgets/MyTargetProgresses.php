<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\RadialBarChart;
use FluxErp\Models\Target;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

class MyTargetProgresses extends RadialBarChart
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?string $end = null;

    public ?string $start = null;

    public ?int $targetId = null;

    #[Locked]
    public ?int $userId = null;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function mount(): void
    {
        $this->userId = auth()->id();
        $this->chart['id'] = data_get($this->chart, 'id', 'apx-' . $this->getId());

        parent::mount();
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.my-target-progresses');
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->resetData();
        $this->calculateChart();
        $this->updateData();
        // When not skipping rerender, we need to force apex charts to reflow
        $chartId = data_get($this->chart, 'id');
        $this->js("requestAnimationFrame(() => { if (window.ApexCharts) { ApexCharts.exec('{$chartId}',
         'updateOptions', {}, false, true); } })");
    }

    public function calculateChart(): void
    {
        $this->max = 100;
        $this->start = $this->getStart()->toDateString();
        $this->end = $this->getEnd()->toDateString();

        $target = resolve_static(Target::class, 'query')
            ->whereKey($this->targetId)
            ->first();

        if ($target) {
            $this->series[] = bcround($this->calculateProgress($target), 2);
            $this->labels[] = $target->name;
        }
    }

    #[Renderless]
    public function updatedTargetId(): void
    {
        $this->calculateByTimeFrame();
    }

    protected function calculateProgress(Target $target): string
    {
        return $target && $this->userId
            ? bcmul(
                bcdiv($target->calculateCurrentValue($this->userId), abs($target->target_value)),
                100
            )
            : 0;
    }

    protected function resetData(): void
    {
        $this->series = [];
        $this->labels = [];
    }
}
