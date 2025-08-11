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
    }

    public function calculateChart(): void
    {
        $this->start = $this->getStart()->toDateString();
        $this->end = $this->getEnd()->toDateString();

        $target = resolve_static(Target::class, 'query')
            ->where('id', $this->targetId)
            ->first();

        if ($target) {
            $modelClass = morphed_model($target->model_type);

            $query = $modelClass::query()
                ->whereBetween($target->timeframe_column, [$target->start_date, $target->end_date]);

            $currentValue = $query->{$target->aggregate_type}($target->aggregate_column);

            $this->series = [];
            $this->labels = [];

            $progress = $target->target_value > 0 ? bcmul(($currentValue / $target->target_value), 100) : 0;
            $this->series[] = bcround($progress, 2);
            $this->labels[] = $target->uuid;

            $this->max = 100;
        }
    }

    public function updatedTargetId(): void
    {
        $this->skipRender();
        $this->calculateByTimeFrame();
    }
}
