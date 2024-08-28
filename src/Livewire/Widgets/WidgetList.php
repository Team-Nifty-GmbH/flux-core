<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class WidgetList extends Component
{
    public string|int $id;

    public function mount(string|int $id): void
    {
        $this->id = $id;
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.widget-list', [
            'availableWidgets' => $this->filterWidgets(Widget::all()),
        ]);
    }

    private function filterWidgets(array $widgets): array
    {
        return array_filter(
            $widgets,
            function (array $widget) {
                $name = $widget['component_name'];

                try {
                    $permissionExists = app(Permission::class)->findByName('widget.'.$name)->exists;
                } catch (PermissionDoesNotExist) {
                    $permissionExists = false;
                }

                return (! $permissionExists || auth()->user()->can('widget.'.$name))
                    && array_key_exists($name, Widget::all());
            }
        );
    }
}
