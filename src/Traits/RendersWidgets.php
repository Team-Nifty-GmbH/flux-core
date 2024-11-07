<?php

namespace FluxErp\Traits;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Facades\Widget;
use FluxErp\Models\Permission;
use FluxErp\Traits\Livewire\EnsureUsedInLivewire;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Js;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait RendersWidgets
{
    use EnsureUsedInLivewire;

    public array $widgets = [];

    public array $availableWidgets = [];

    public array $params = [
        'timeFrame' => TimeFrameEnum::ThisMonth,
        'start' => null,
        'end' => null,
    ];

    public function mountRendersWidgets(): void
    {
        $this->availableWidgets = $this->filterWidgets(Widget::all());
        $this->widgets();
    }

    #[Computed]
    public function availableWidgets(): array
    {
        return $this->filterWidgets(Widget::all());
    }

    public function updatedParams(): void
    {
        $this->skipRender();
    }

    #[Renderless]
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
    public function saveWidgets(array $widgets): void
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

    public function resetWidgets(): void
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

    protected function filterWidgets(array $widgets): array
    {
        $widgets = array_filter(
            $widgets,
            function (array $widget) {
                $name = $widget['component_name'];

                try {
                    $permissionExists = ! is_null(
                        resolve_static(
                            Permission::class,
                            'findByName',
                            [
                                'name' => 'widget.' . $name,
                            ]
                        )
                    );
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
