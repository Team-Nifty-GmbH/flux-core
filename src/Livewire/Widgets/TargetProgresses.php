<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\RadialBarChart;
use FluxErp\Models\Target;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;

class TargetProgresses extends RadialBarChart
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?string $end = null;

    public ?string $start = null;

    public ?int $targetId = null;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.target-progresses');
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
        $this->resetData();
    }

    public function calculateChart(): void
    {
        $this->max = 100;
        $this->start = $this->getStart()->toDateString();
        $this->end = $this->getEnd()->toDateString();

        $target = resolve_static(Target::class, 'query')
            ->where('id', $this->targetId)
            ->first();

        if ($target) {
            $this->series[] = bcround($this->calculateProgress($target), 2);
            $this->labels[] = $target->uuid;
        }
    }

    public function updatedTargetId(): void
    {
        $this->skipRender();
        $this->calculateByTimeFrame();
    }

    private function calculateProgress(Target $target): string
    {
        return $target && $target->target_value > 0
            ? bcmul(
                bcdiv($target->calculateCurrentValue(auth()->id()), $target->target_value),
                100
            )
            : 0;
    }

    private function resetData(): void
    {
        $this->series = [];
        $this->labels = [];
    }
}
