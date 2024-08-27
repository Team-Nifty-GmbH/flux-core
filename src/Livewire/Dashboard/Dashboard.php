<?php

namespace FluxErp\Livewire\Dashboard;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Js;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use WireUi\Traits\Actions;

class Dashboard extends Component
{
    use Actions;

    public array $widgets = [];

    public array $availableWidgets = [];

    public TimeFrameEnum $timeFrame = TimeFrameEnum::ThisMonth;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    public function mount(): void
    {
        $this->availableWidgets = $this->filterWidgets(Widget::all());
        $this->widgets();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard', [
            'timeFrames' => array_map(function (TimeFrameEnum $timeFrame) {
                return [
                    'value' => $timeFrame->value,
                    'label' => __($timeFrame->value),
                ];
            }, TimeFrameEnum::cases()),
        ]);
    }

    public function updatedTimeFrame(): void
    {
        $this->skipRender();
    }

    public function updatedStart(): void
    {
        $this->start = $this->start->startOfDay();
        if ($this->start->greaterThan($this->end)) {
            $this->end = $this->start->copy()->addDays(30);
        }

        $this->dispatchStartEnd();

        $this->skipRender();
    }

    public function updatedEnd(): void
    {
        $this->end = $this->end->endOfDay();
        if ($this->start->greaterThan($this->end)) {
            $this->start = $this->end->copy()->subDays(30);
        }

        $this->dispatchStartEnd();

        $this->skipRender();
    }

    protected function dispatchStartEnd(): void
    {
        if ($this->timeFrame === TimeFrameEnum::Custom && $this->start && $this->end) {
            $this->dispatch('time-frame-changed', $this->timeFrame, $this->start->toDateString(), $this->end->toDateString());
        }
    }

    public function widgets(): void
    {
        $this->widgets = $this->filterWidgets(auth()->user()->widgets()->get()->toArray());
    }

    #[Renderless]
    public function syncWidgets(array $widgets): void
    {
        $this->widgets = $widgets;
    }

    #[Renderless]
    public function saveDashboard(array $widgets): void
    {
        $this->widgets = $widgets;

        $existingItemIds = array_filter(Arr::pluck($this->widgets, 'id'), 'is_numeric');
        auth()->user()->widgets()->whereNotIn('id', $existingItemIds)->delete();

        // create new widgets, update existing widgets
        foreach ($this->widgets as &$widget) {
            $savedWidget = auth()->user()->widgets()->updateOrCreate(['id' => $widget['id']], $widget);
            $widget['id'] = $savedWidget->id;
        }

        $this->widgets();
    }

    public function cancelDashboard(): void
    {
        $this->widgets();
    }

    #[Js]
    public function disableEditMode(): void
    {
        $this->js(<<<'JS'
            isLoading = true;
            editGridMode(false);
        JS);
    }

    #[Renderless]
    public function showFlashMessage(): void
    {
        $this->notification()->success(__('Dashboard syncing'));
    }

    protected function filterWidgets(array $widgets): array
    {
        $widgets = array_filter(
            $widgets,
            function (array $widget) {
                $name = $widget['component_name'];

                try {
                    $permissionExists = resolve_static(
                        Permission::class,
                        'findByName',
                        [
                            'name' => 'widget.' . $name,
                        ]
                    )->exists;
                } catch (PermissionDoesNotExist) {
                    $permissionExists = false;
                }

                return (! $permissionExists || auth()->user()->can('widget.' . $name))
                    && array_key_exists($name, Widget::all());
            }
        );

        ksort($widgets);

        return $widgets;
    }
}
