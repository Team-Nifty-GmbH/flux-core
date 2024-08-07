<?php

namespace FluxErp\Livewire\Dashboard;

use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use FluxErp\Models\Widget as WidgetModel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use WireUi\Traits\Actions;

class Dashboard extends Component
{
    use Actions;

    public array $widgets = [];

    public function mount(): void
    {
        $this->widgets();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard', [
            'availableWidgets' => $this->filterWidgets(Widget::all()),
        ]);
    }

    public function widgets(): void
    {
        $this->widgets = $this->filterWidgets(auth()->user()->widgets()->get()->toArray());
    }

    #[Renderless]
    public function saveDashboard(array $sortedIds = []): void
    {
        $existingItemIds = array_filter(Arr::pluck($this->widgets, 'id'), 'is_numeric');
        auth()->user()->widgets()->whereNotIn('id', $existingItemIds)->delete();

        // create new widgets, update existing widgets
        foreach ($this->widgets as &$widget) {
            $savedWidget = auth()->user()->widgets()->updateOrCreate(['id' => $widget['id']], $widget);
            $position = array_search($widget['id'], $sortedIds);

            if ($position !== false) {
                $sortedIds[$position] = $savedWidget->id;
            }

            $widget['id'] = $savedWidget->id;
        }

        $sortedIds = array_filter($sortedIds, 'is_numeric');
        app(WidgetModel::class)->setNewOrder($sortedIds);

        $this->widgets();
    }

    private function filterWidgets(array $widgets): array
    {
        return array_filter(
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
    }
}
