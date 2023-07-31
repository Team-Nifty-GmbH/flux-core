<?php

namespace FluxErp\Http\Livewire\Dashboard;

use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
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

    public function saveWidgets(array $itemIds): void
    {
        $existingItemIds = array_filter($itemIds, 'is_numeric');
        auth()->user()->widgets()->whereNotIn('id', $existingItemIds)->delete();
        \FluxErp\Models\Widget::setNewOrder($existingItemIds);
        $newItemIds = array_filter(array_map(function ($id) {
            $componentName = substr($id, 4);

            return str_starts_with($id, 'new-')
                ? [
                    'name' => Str::headline($componentName),
                    'component_name' => $componentName,
                ]
                : null;
        }, $itemIds));

        if ($newItemIds) {
            auth()->user()->widgets()->createMany(array_filter($newItemIds));
        }

        $this->widgets();
    }

    public function updateWidget(array $widget): void
    {
        $widgetModel = \FluxErp\Models\Widget::query()
            ->whereKey($widget['id'])
            ->firstOrFail();
        $widgetModel->fill($widget);
        $widgetModel->save();

        $this->widgets();
    }

    private function filterWidgets(array $widgets): array
    {
        return array_filter(
            $widgets,
            function (array $widget) {
                $name = $widget['component_name'] ?? $widget['name'];

                try {
                    $permissionExists = Permission::findByName('widget.' . $name)->exists;
                } catch (PermissionDoesNotExist $e) {
                    $permissionExists = false;
                }

                return (! $permissionExists || auth()->user()->can('widget.' . $name))
                    && array_key_exists($name, Widget::all());
            }
        );
    }
}
