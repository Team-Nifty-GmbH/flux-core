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
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Throwable;

class GenerateWidgetWizard extends Component
{
    use Actions;

    public int $step = 1;

    #[Url]
    public ?string $datatable = null;

    #[Locked]
    public array $userFilters = [];

    #[Locked]
    public array $availableColumns = [];

    public ?string $widgetType = null;

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

    public function mount(): void
    {
        if (! $this->datatable || ! class_exists($this->datatable)) {
            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        if (! in_array(HasWidgetGeneration::class, class_uses_recursive($this->datatable))) {
            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        $datatable = app()->make($this->datatable);
        $this->availableColumns = $datatable->buildAvailableColumns();

        $filters = session()->pull('widget-wizard-filters', []);
        $this->userFilters = $filters;
    }

    public function render(): View
    {
        return view('flux::livewire.datatables.generate-widget-wizard');
    }

    public function nextStep(): void
    {
        match ($this->step) {
            2 => $this->validate(['widgetType' => 'required|in:value_box,bar_chart,line_chart,value_list']),
            3 => $this->validateStepThree(),
            default => null,
        };

        $this->step = min($this->step + 1, 4);
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'targetDashboard' => 'required|string',
        ]);

        auth()->user()->widgets()->create([
            'component_name' => $this->resolveComponentName(),
            'dashboard_component' => $this->targetDashboard,
            'name' => $this->name,
            'config' => array_filter([
                'type' => $this->widgetType,
                'datatable' => $this->datatable,
                'name' => $this->name,
                'filters' => $this->userFilters,
                'value_column' => $this->valueColumn,
                'aggregate' => $this->aggregate,
                'group_column' => $this->groupColumn,
                'date_column' => $this->widgetType === 'line_chart'
                    ? $this->dateColumn
                    : ($this->timeframeAware ? $this->timeframeDateColumn : null),
                'timeframe_aware' => $this->timeframeAware,
                'columns' => $this->selectedColumns ?: null,
                'sort_column' => $this->sortColumn,
                'sort_direction' => $this->sortDirection,
                'limit' => $this->widgetType === 'value_list' ? $this->limit : null,
                'is_shared' => $this->isShared,
            ], fn ($v) => ! is_null($v)),
            'width' => 2,
            'height' => 2,
            'order_column' => 0,
            'order_row' => 0,
        ]);

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
        $livewireNamespace = 'FluxErp\\Livewire\\';
        $directoryPath = flux_path('src/Livewire');

        try {
            if (is_dir($directoryPath)) {
                $iterator = Finder::create()
                    ->in($directoryPath)
                    ->files()
                    ->name('*.php')
                    ->sortByName();

                foreach ($iterator as $file) {
                    $relativePath = ltrim(Str::replace($directoryPath, '', $file->getRealPath()), '/');
                    $class = $livewireNamespace . str_replace(['/', '.php'], ['\\', ''], $relativePath);

                    if (! class_exists($class)) {
                        continue;
                    }

                    $reflection = new ReflectionClass($class);

                    if (! $reflection->isAbstract() && $reflection->isSubclassOf(Dashboard::class)) {
                        $dashboards[$class] = __(class_basename($class));
                    }
                }
            }
        } catch (Throwable) {
            // Fallback
        }

        if (empty($dashboards)) {
            $dashboards[\FluxErp\Livewire\Dashboard\Dashboard::class] = __('Dashboard');
        }

        return $dashboards;
    }

    protected function resolveComponentName(): string
    {
        $classMap = [
            'value_box' => GeneratedValueBox::class,
            'bar_chart' => GeneratedBarChart::class,
            'line_chart' => GeneratedLineChart::class,
            'value_list' => GeneratedValueList::class,
        ];

        $class = $classMap[$this->widgetType] ?? GeneratedValueBox::class;

        return app('livewire.finder')->normalizeName($class);
    }

    protected function validateStepThree(): void
    {
        $rules = match ($this->widgetType) {
            'value_box' => [
                'valueColumn' => 'required_unless:aggregate,count',
                'aggregate' => 'required|in:sum,avg,min,max,count',
            ],
            'bar_chart' => [
                'groupColumn' => 'required|string',
                'valueColumn' => 'required_unless:aggregate,count',
                'aggregate' => 'required|in:sum,avg,min,max,count',
            ],
            'line_chart' => [
                'dateColumn' => 'required|string',
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
