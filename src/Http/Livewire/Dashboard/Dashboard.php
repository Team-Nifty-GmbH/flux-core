<?php

namespace FluxErp\Http\Livewire\Dashboard;

use FluxErp\Facades\Widget;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
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
            'availableWidgets' => Widget::all(),
        ]);
    }

    public function widgets(): void
    {
        $this->widgets = auth()
            ->user()
            ->widgets()
            ->get()
            ->filter(function ($widget) {
                return array_key_exists($widget->component_name, Widget::all());
            })
            ->toArray();
    }

    public function saveWidgets(array $itemIds): void
    {
        $existingItemIds = array_filter($itemIds, 'is_numeric');
        auth()->user()->widgets()->whereNotIn('id', $existingItemIds)->delete();
        \FluxErp\Models\Widget::setNewOrder($existingItemIds);
        $newItemIds = array_filter(array_map(function ($id) use ($itemIds) {
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
}
