<?php

namespace FluxErp\Http\Livewire\Dashboard;

use FluxErp\Facades\Widget;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Dashboard extends Component
{
    use Actions;

    public array $widgets = [];

    public function mount()
    {
        $this->widgets();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard', [
            'availableWidgets' => Widget::all(),
        ]);
    }

    public function widgets()
    {
        $this->widgets = auth()->user()->widgets->toArray();
    }

    public function saveWidgets(array $itemIds): void
    {
        $eixistingItemIds = array_filter($itemIds, 'is_numeric');
        auth()->user()->widgets()->whereNotIn('id', $eixistingItemIds)->delete();
        \FluxErp\Models\Widget::setNewOrder($eixistingItemIds);

        $newItemIds = array_filter(array_map(function ($id) {
            return str_starts_with($id, 'new-')
                ? [
                    'name' => 'TestWidget',
                    'component_name' => substr($id, 4),
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
        $widgetModel = \FluxErp\Models\Widget::query()->whereKey($widget['id'])->firstOrFail();
        $widgetModel->fill($widget);
        $widgetModel->save();

        $this->widgets();
    }
}
