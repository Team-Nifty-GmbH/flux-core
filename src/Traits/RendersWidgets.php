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

    protected static ?array $defaultWidgets = null;

    public array $availableWidgets = [];

    public bool $isLoading = false;

    public array $params = [
        'timeFrame' => TimeFrameEnum::ThisMonth,
        'start' => null,
        'end' => null,
    ];

    public array $widgets = [];

    #[Computed]
    public function availableWidgets(): array
    {
        return $this->filterWidgets(Widget::all());
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
    public function mountRendersWidgets(): void
    {
        $this->availableWidgets = $this->filterWidgets(Widget::all());
        $this->widgets();
    }

    #[Renderless]
    public function resetWidgets(): void
    {
        $this->widgets();
    }

    #[Renderless]
    public function saveWidgets(?array $widgets = null): void
    {
        $this->widgets = $widgets;

        $existingItemIds = array_filter(Arr::pluck($this->widgets, 'id'), 'is_numeric');
        auth()
            ->user()
            ->widgets()
            ->where('dashboard_component', static::class)
            ->whereNotIn('id', $existingItemIds)
            ->delete();

        // create new widgets, update existing widgets
        foreach ($this->widgets as &$widget) {
            $savedWidget = auth()
                ->user()
                ->widgets()
                ->updateOrCreate(
                    ['id' => is_numeric(data_get($widget, 'id')) ? data_get($widget, 'id') : null],
                    array_merge(
                        ['dashboard_component' => static::class],
                        Arr::except($widget, 'id')
                    )
                );
            $widget['id'] = $savedWidget->id;
        }

        $this->widgets();
    }

    #[Renderless]
    public function syncWidgets(array $widgets): void
    {
        $this->widgets = $widgets;
    }

    public function updatedParams(): void
    {
        $this->skipRender();
    }

    #[Renderless]
    public function widgets(): void
    {
        $this->widgets = array_values(
            $this->filterWidgets(
                auth()
                    ->user()
                    ?->widgets()
                    ->where('dashboard_component', static::class)
                    ->get()
                    ->toArray() ?: static::getDefaultWidgets() ?? []
            )
        );
    }

    #[Renderless]
    public function wireModel(): string
    {
        return 'params';
    }

    #[Renderless]
    protected function filterWidgets(array $widgets): array
    {
        $widgets = array_filter(
            $widgets,
            function (array $widget) {
                $name = $widget['component_name'];

                if (
                    collect(Arr::wrap(data_get($widget, 'dashboard_component')))
                        ->map(fn (string $dashboardClass) => resolve_static($dashboardClass, 'class'))
                        ->doesntContain(static::class)
                ) {
                    return false;
                }

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
