<?php

namespace FluxErp\Traits\Livewire\Dashboard;

use FluxErp\Enums\ComparisonTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Facades\Widget;
use FluxErp\Models\Role;
use FluxErp\Models\Widget as WidgetModel;
use FluxErp\Support\Auth\PermissionSet;
use FluxErp\Traits\Livewire\EnsureUsedInLivewire;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Js;
use Livewire\Attributes\Renderless;

trait RendersWidgets
{
    use EnsureUsedInLivewire;

    protected static ?array $defaultWidgets = null;

    protected static array $registeredPresets = [];

    public array $availableWidgets = [];

    public array $availableWidgetsTree = [];

    public ?int $dashboardRoleId = null;

    public array $presets = [];

    public array $params = [
        'timeFrame' => TimeFrameEnum::ThisMonth,
        'dateRange' => [],
        'comparisonType' => ComparisonTypeEnum::Auto,
        'comparisonRange' => [],
    ];

    public bool $sync = false;

    public array $widgets = [];

    public static function getPresets(): array
    {
        return static::$registeredPresets[static::class] ?? [];
    }

    public static function registerPreset(string $name, array $widgets): void
    {
        static::$registeredPresets[static::class][$name] = $widgets;
    }

    public function applyPreset(string $name): void
    {
        $widgets = data_get(static::getPresets(), $name);

        abort_if(is_null($widgets), 404);

        foreach (collect($widgets)->groupBy('group') as $group => $groupWidgets) {
            $this->group = $group === '' ? null : $group;
            $this->saveWidgets(static::mapDefaultWidgets(
                $groupWidgets
                    ->map(fn (array $widget): array => array_merge(
                        ['width' => 1, 'height' => 1, 'order_column' => 0, 'order_row' => 0],
                        $widget
                    ))
                    ->all()
            ));
        }

        $this->widgets();
        $this->dispatch('gridstack-reinit');
    }

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
        $this->availableWidgetsTree = $this->availableWidgetsTree();
        $this->presets = array_keys(static::getPresets());
        $this->widgets();
    }

    public function resetToRoleDashboard(): void
    {
        auth()
            ->user()
            ->widgets()
            ->where('dashboard_component', static::class)
            ->where('group', $this->group)
            ->delete();

        $this->widgets();
        $this->dispatch('gridstack-reinit');
    }

    #[Renderless]
    public function resetWidgets(): void
    {
        $this->widgets();
    }

    #[Renderless]
    public function saveWidgets(?array $widgets = null): void
    {
        $widgetsToSave = array_values($widgets ?? $this->widgets);

        $existingItemIds = array_filter(Arr::pluck($widgetsToSave, 'id'), 'is_numeric');

        auth()
            ->user()
            ->widgets()
            ->where('dashboard_component', static::class)
            ->where('group', $this->group ?? null)
            ->whereNotIn('id', $existingItemIds)
            ->delete();

        // create new widgets, update existing widgets
        $savedWidgets = [];
        foreach ($widgetsToSave as $widget) {
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
            $mergedWidget = array_merge($widget, $savedWidget->toArray());
            $mergedWidget['id'] = $savedWidget->id;
            $savedWidgets[] = $mergedWidget;
        }

        $this->widgets = array_values(array_merge(
            array_filter($this->widgets, fn (array $widget) => data_get($widget, 'group') !== $this->group),
            $savedWidgets
        ));

        if (! $widgetsToSave) {
            $this->widgets();
        }
    }

    #[Renderless]
    public function saveAsRoleDashboard(?int $roleId): void
    {
        abort_unless($this->canEditRoleDashboards(), 403);

        $role = resolve_static(Role::class, 'query')
            ->whereKey($roleId)
            ->firstOrFail();

        DB::transaction(function () use ($role): void {
            $role->widgets()
                ->where('dashboard_component', static::class)
                ->delete();
            $role->widgets()
                ->createMany(
                    array_map(
                        fn (array $widget): array => array_merge(
                            $this->copyableWidgetAttributes($widget),
                            ['dashboard_component' => static::class]
                        ),
                        $this->widgets
                    )
                );
        });

        $this->toast()
            ->success(__('The dashboard was saved as default for the role :role.', ['role' => $role->name]))
            ->send();
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
        $this->dispatch('dashboard-params-updated', params: $this->params);
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

        $roleWidgets = $this->roleWidgets();
        $defaultWidgets = collect(static::getDefaultWidgets() ?? [])
            ->groupBy(fn (array $widget): ?string => data_get($widget, 'group'));
        $groups = $defaultWidgets->keys()
            ->merge($roleWidgets->keys())
            ->merge($userWidgets?->keys() ?? [])
            ->unique();

        $allWidgets = [];
        foreach ($groups as $group) {
            $groupWidgets = $userWidgets?->get($group)
                ?? $roleWidgets->get($group)
                ?? $defaultWidgets->get($group);

            $allWidgets = array_merge($allWidgets, $groupWidgets->toArray());
        }

        $this->widgets = array_values($this->filterWidgets($allWidgets));
    }

    #[Renderless]
    public function wireModel(): string
    {
        return 'params';
    }

    protected function availableWidgetsTree(): array
    {
        $toNode = fn (array $widget) => [
            'id' => 'widget-' . $widget['component_name'],
            'label' => __($widget['label']),
            'component_name' => $widget['component_name'],
        ];

        $widgets = collect($this->availableWidgets);

        $categorized = $widgets
            ->whereNotNull('category')
            ->groupBy('category')
            ->sortKeys()
            ->map(fn ($children, $category) => [
                'id' => 'category-' . $category,
                'label' => __($category),
                'children' => $children
                    ->map($toNode)
                    ->sortBy('label', SORT_STRING | SORT_FLAG_CASE)
                    ->values()
                    ->all(),
            ])
            ->values();

        $uncategorized = $widgets
            ->whereNull('category')
            ->map($toNode)
            ->sortBy('label', SORT_STRING | SORT_FLAG_CASE)
            ->values();

        return $categorized->merge($uncategorized)->all();
    }

    protected function canEditRoleDashboards(): bool
    {
        return PermissionSet::make()->allows(route_to_permission('settings.permissions', false));
    }

    protected function copyableWidgetAttributes(array $widget): array
    {
        return Arr::only(
            $widget,
            [
                'component_name',
                'dashboard_component',
                'name',
                'config',
                'group',
                'width',
                'height',
                'order_column',
                'order_row',
            ]
        );
    }

    protected function roleWidgets(): Collection
    {
        $roleIds = auth()
            ->user()
            ?->roles
            ->modelKeys() ?? [];

        if (! $roleIds) {
            return collect();
        }

        return resolve_static(WidgetModel::class, 'query')
            ->where('widgetable_type', morph_alias(Role::class))
            ->whereIntegerInRaw('widgetable_id', $roleIds)
            ->where('dashboard_component', static::class)
            ->orderBy('widgetable_id')
            ->get()
            ->groupBy('group')
            ->map(fn (Collection $widgets): Collection => $widgets
                ->where('widgetable_id', $widgets->first()->widgetable_id)
                ->map(fn (WidgetModel $widget): array => array_merge(
                    $this->copyableWidgetAttributes($widget->toArray()),
                    ['id' => Str::uuid()->toString()]
                ))
                ->values()
            );
    }

    protected function filterWidgets(array $widgets): array
    {
        $permissions = PermissionSet::make();

        $widgets = array_filter(
            $widgets,
            fn (array $widget): bool => collect(Arr::wrap(data_get($widget, 'dashboard_component')))
                ->map(fn (string $dashboardClass): string => resolve_static($dashboardClass, 'class'))
                ->contains(static::class)
                && $permissions->allows('widget.' . $widget['component_name'])
                && ! is_null(Widget::get($widget['component_name']))
        );

        ksort($widgets);

        return $widgets;
    }
}
