<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Livewire\Support\Dashboard;
use FluxErp\Livewire\Widgets\Generated\GeneratedBarChart;
use FluxErp\Livewire\Widgets\Generated\GeneratedLineChart;
use FluxErp\Livewire\Widgets\Generated\GeneratedValueBox;
use FluxErp\Livewire\Widgets\Generated\GeneratedValueList;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTable\HasWidgetGeneration;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Livewire;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Throwable;

class GenerateWidgetWizard extends Component
{
    use Actions;

    public int $step = 1;

    #[Url]
    #[Locked]
    public ?string $datatable = null;

    #[Locked]
    public array $userFilters = [];

    #[Locked]
    public array $availableColumns = [];

    public ?string $widgetType = null;

    public bool $showTotals = true;

    public bool $horizontalBars = false;

    public string $curveStyle = 'smooth';

    public string $timeGrouping = 'month';

    public string $pieStyle = 'pie';

    public ?string $valueColumn = null;

    public ?string $aggregate = 'sum';

    public ?string $groupColumn = null;

    public ?string $dateColumn = null;

    public array $selectedColumns = [];

    public ?string $sortColumn = null;

    public string $sortDirection = 'desc';

    public int $limit = 10;

    public string $name = '';

    public ?string $targetDashboard = null;

    public bool $timeframeAware = false;

    public ?string $timeframeDateColumn = null;

    public bool $isShared = false;

    public array $previewData = [];

    public function mount(): void
    {
        if (! $this->datatable || ! class_exists($this->datatable)) {
            session()->forget('widget-wizard-state');

            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        if (! in_array(HasWidgetGeneration::class, class_uses_recursive($this->datatable))) {
            session()->forget('widget-wizard-state');

            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        $datatable = app($this->datatable);
        $this->availableColumns = $datatable->buildAvailableColumns();

        $freshFilters = session()->pull('widget-wizard-filters');

        if (! is_null($freshFilters)) {
            $this->userFilters = $freshFilters;
            session()->forget('widget-wizard-state');
        } else {
            $saved = session()->get('widget-wizard-state', []);

            if (data_get($saved, 'datatable') === $this->datatable) {
                foreach ($saved as $key => $value) {
                    if (property_exists($this, $key) && $key !== 'datatable' && $key !== 'availableColumns') {
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    public function render(): View
    {
        return view('flux::livewire.datatables.generate-widget-wizard');
    }

    public function generatePreview(): void
    {
        $config = $this->getPreviewConfig();
        $className = match ($this->widgetType) {
            'value_box' => GeneratedValueBox::class,
            'bar_chart', 'pie_chart' => GeneratedBarChart::class,
            'line_chart', 'area_chart' => GeneratedLineChart::class,
            'value_list' => GeneratedValueList::class,
            default => null,
        };

        if (is_null($className)) {
            return;
        }

        try {
            $widget = Livewire::new($className, ['config' => $config]);

            $this->previewData = match ($this->widgetType) {
                'value_box' => [
                    'type' => 'value_box',
                    'sum' => $widget->sum,
                    'previousSum' => $widget->previousSum,
                    'growthRate' => $widget->growthRate,
                ],
                'bar_chart', 'pie_chart', 'line_chart', 'area_chart' => [
                    'type' => $this->widgetType,
                    'series' => $widget->series ?? [],
                    'categories' => data_get($widget->xaxis, 'categories', []),
                ],
                'value_list' => [
                    'type' => 'value_list',
                    'items' => $widget->items ?? [],
                ],
                default => [],
            };
        } catch (Throwable $e) {
            $this->previewData = ['type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function nextStep(): void
    {
        match ($this->step) {
            2 => $this->validate(['widgetType' => 'required|in:value_box,bar_chart,line_chart,area_chart,pie_chart,value_list']),
            3 => $this->validateStepThree(),
            4 => $this->validate([
                'name' => 'required|string|max:255',
                'targetDashboard' => ['required', 'string', 'in:' . implode(',', array_keys($this->getAvailableDashboards()))],
            ]),
            default => null,
        };

        $this->step = min($this->step + 1, 5);

        if ($this->step === 5) {
            session()->put('widget-preview-config', [
                'component' => $this->resolveComponentName(),
                'config' => $this->getPreviewConfig(),
            ]);
        }

        $this->persistWizardState();
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
        $this->persistWizardState();
    }

    public function getPreviewConfig(): array
    {
        return array_filter([
            'type' => $this->widgetType,
            'datatable' => $this->datatable,
            'name' => $this->name,
            'filters' => $this->userFilters,
            'value_column' => $this->valueColumn,
            'aggregate' => $this->aggregate,
            'group_column' => $this->groupColumn,
            'date_column' => in_array($this->widgetType, ['line_chart', 'area_chart'])
                ? $this->dateColumn
                : ($this->timeframeAware ? $this->timeframeDateColumn : null),
            'timeframe_aware' => $this->timeframeAware,
            'show_totals' => $this->showTotals,
            'horizontal_bars' => $this->horizontalBars,
            'curve_style' => $this->curveStyle,
            'time_grouping' => $this->timeGrouping,
            'pie_style' => $this->pieStyle,
            'columns' => $this->selectedColumns ?: null,
            'sort_column' => $this->sortColumn,
            'sort_direction' => $this->sortDirection,
            'limit' => $this->widgetType === 'value_list' ? $this->limit : null,
            'is_shared' => $this->isShared,
        ], fn ($v) => ! is_null($v));
    }

    public function cancel(): void
    {
        session()->forget('widget-wizard-state');

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function save(): void
    {
        $this->validate([
            'widgetType' => 'required|in:value_box,bar_chart,line_chart,area_chart,pie_chart,value_list',
            'name' => 'required|string|max:255',
            'targetDashboard' => ['required', 'string', 'in:' . implode(',', array_keys($this->getAvailableDashboards()))],
        ]);

        auth()->user()->widgets()->create([
            'component_name' => $this->resolveComponentName(),
            'dashboard_component' => $this->targetDashboard,
            'name' => $this->name,
            'config' => $this->getPreviewConfig(),
            'width' => 2,
            'height' => 2,
            'order_column' => 0,
            'order_row' => 0,
        ]);

        session()->forget('widget-wizard-state');

        $this->toast()
            ->success(__('Widget created successfully.'))
            ->send();

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function getNumericColumns(): array
    {
        return array_values(array_filter(
            $this->availableColumns,
            fn (array $column) => data_get($column, 'type') === 'numeric'
        ));
    }

    public function getDateColumns(): array
    {
        return array_values(array_filter(
            $this->availableColumns,
            fn (array $column) => data_get($column, 'type') === 'date'
        ));
    }

    #[Computed]
    public function getAvailableDashboards(): array
    {
        $dashboards = [];

        $scanPaths = [
            'FluxErp\\Livewire\\' => flux_path('src/Livewire'),
            config('livewire.class_namespace', 'App\\Livewire') . '\\' => app_path('Livewire'),
        ];

        foreach ($scanPaths as $namespace => $directoryPath) {
            try {
                if (! is_dir($directoryPath)) {
                    continue;
                }

                $iterator = Finder::create()
                    ->in($directoryPath)
                    ->files()
                    ->name('*.php')
                    ->sortByName();

                foreach ($iterator as $file) {
                    $relativePath = ltrim(Str::replace($directoryPath, '', $file->getRealPath()), '/');
                    $class = $namespace . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                    if (! class_exists($class)) {
                        continue;
                    }

                    $reflection = new ReflectionClass($class);

                    if (! $reflection->isAbstract() && $reflection->isSubclassOf(Dashboard::class)) {
                        $dashboards[$class] = __(
                            Str::of($class)
                                ->afterLast('\\Livewire\\')
                                ->replace('\\', ' → ')
                                ->headline()
                                ->toString()
                        );
                    }
                }
            } catch (Throwable) {
                // Fallback
            }
        }

        if (empty($dashboards)) {
            $dashboards[\FluxErp\Livewire\Dashboard\Dashboard::class] = __('Dashboard');
        }

        return $dashboards;
    }

    public function resolveComponentName(): string
    {
        $classMap = [
            'value_box' => GeneratedValueBox::class,
            'bar_chart' => GeneratedBarChart::class,
            'line_chart' => GeneratedLineChart::class,
            'area_chart' => GeneratedLineChart::class,
            'pie_chart' => GeneratedBarChart::class,
            'value_list' => GeneratedValueList::class,
        ];

        $class = $classMap[$this->widgetType] ?? throw new \InvalidArgumentException("Unknown widget type: {$this->widgetType}");

        return app('livewire.finder')->normalizeName($class);
    }

    protected function persistWizardState(): void
    {
        session()->put('widget-wizard-state', collect(get_object_vars($this))
            ->only([
                'datatable', 'step', 'userFilters', 'widgetType', 'showTotals',
                'horizontalBars', 'curveStyle', 'timeGrouping', 'pieStyle', 'valueColumn',
                'aggregate', 'groupColumn', 'dateColumn', 'selectedColumns',
                'sortColumn', 'sortDirection', 'limit', 'name', 'targetDashboard',
                'timeframeAware', 'timeframeDateColumn', 'isShared',
            ])
            ->toArray()
        );
    }

    protected function validateStepThree(): void
    {
        $rules = match ($this->widgetType) {
            'value_box' => [
                'valueColumn' => 'required_unless:aggregate,count',
                'aggregate' => 'required|in:sum,avg,min,max,count',
            ],
            'bar_chart', 'line_chart', 'area_chart', 'pie_chart' => [
                'groupColumn' => 'required|string',
                'valueColumn' => 'required_unless:aggregate,count',
                'aggregate' => 'required|in:sum,avg,min,max,count',
            ],
            'value_list' => [
                'selectedColumns' => 'required|array|min:1',
                'sortColumn' => 'nullable|string',
                'sortDirection' => 'required|in:asc,desc',
                'limit' => 'required|integer|min:1|max:100',
            ],
            default => [],
        };

        $this->validate($rules);
    }
}
