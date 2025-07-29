<?php

namespace FluxErp\Traits\Livewire\Dashboard;

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

    public array $params = [
        'timeFrame' => TimeFrameEnum::ThisMonth,
        'start' => null,
        'end' => null,
    ];

    public bool $sync = false;

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
        $widgetsToSave = $widgets ?? $this->widgets;

        $this->widgets = array_merge(
            array_filter($this->widgets, fn (array $widget) => data_get($widget, 'group') !== $this->group),
            $widgetsToSave
        );

        $existingItemIds = array_filter(Arr::pluck($widgetsToSave, 'id'), 'is_numeric');

        auth()
            ->user()
            ->widgets()
            ->where('dashboard_component', static::class)
            ->where('group', $this->group ?? null)
            ->whereNotIn('id', $existingItemIds)
            ->delete();

        // create new widgets, update existing widgets
        foreach ($widgetsToSave as &$widget) {
            $savedWidget = auth()
                ->user()
                ->widgets()
                ->updateOrCreate(
                    ['id' => is_numeric(data_get($widget, 'id')) ? data_get($widget, 'id') : null],
                    array_merge(
                        [
                            'dashboard_component' => static::class,
                            'group' => $this->group ?? null,
                        ],
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
        $this->widgets = array_merge(
            array_filter($this->widgets, fn (array $widget) => data_get($widget, 'group') !== $this->group),
            $widgets
        );
    }

    public function updatedParams(): void
    {
        $this->skipRender();
    }

    #[Renderless]
    public function widgets(): void
    {
        $userWidgets = auth()
            ->user()
            ?->widgets()
            ->where('dashboard_component', static::class)
            ->get()
            ->groupBy('group');

        $allWidgets = [];

        $defaultWidgets = static::getDefaultWidgets() ?? [];
        $defaultGroups = collect($defaultWidgets)->pluck('group')->unique()->filter();
        $userGroups = $userWidgets ? $userWidgets->keys() : collect();
        $allGroups = $defaultGroups->merge($userGroups)->unique();

        foreach ($allGroups as $group) {
            if ($userWidgets && $userWidgets->has($group)) {
                $allWidgets = array_merge($allWidgets, $userWidgets->get($group)->toArray());
            } else {
                $groupDefaults = collect($defaultWidgets)
                    ->filter(fn (array $widget) => data_get($widget, 'group') === $group)
                    ->toArray();
                $allWidgets = array_merge($allWidgets, $groupDefaults);
            }
        }

        if ($userWidgets && $userWidgets->has(null)) {
            $allWidgets = array_merge($allWidgets, $userWidgets->get(null)->toArray());
        } else {
            $nullGroupDefaults = collect($defaultWidgets)
                ->filter(fn (array $widget) => is_null(data_get($widget, 'group')))
                ->toArray();
            $allWidgets = array_merge($allWidgets, $nullGroupDefaults);
        }

        $this->widgets = array_values($this->filterWidgets($allWidgets));
    }

    #[Renderless]
    public function wireModel(): string
    {
        return 'params';
    }

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
