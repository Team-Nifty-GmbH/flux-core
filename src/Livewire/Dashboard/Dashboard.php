<?php

namespace FluxErp\Livewire\Dashboard;

use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
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

    public array $availableWidgets = [];

    public function mount(): void
    {

        $this->availableWidgets = $this->filterWidgets(Widget::all());
        $this->widgets();

    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard');
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

    #[Renderless]
    public function cancelDashboard(): void
    {
        $this->widgets();
    }

    private function filterWidgets(array $widgets): array
    {
        return array_filter(
            $widgets,
            function (array $widget) {
                $name = $widget['component_name'];

                try {
                    $permissionExists = app(Permission::class)->findByName('widget.' . $name)->exists;
                } catch (PermissionDoesNotExist) {
                    $permissionExists = false;
                }

                return (! $permissionExists || auth()->user()->can('widget.' . $name))
                    && array_key_exists($name, Widget::all());
            }
        );
    }

    #[Renderless]
    public function showFlashMessage(): void
    {
        $this->notification()->success(__('Dashboard syncing'));
    }
}
