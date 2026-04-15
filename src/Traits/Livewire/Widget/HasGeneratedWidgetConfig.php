<?php

namespace FluxErp\Traits\Livewire\Widget;

use FluxErp\Traits\Livewire\DataTable\HasWidgetGeneration;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Formatters\BooleanFormatter;
use TeamNiftyGmbH\DataTable\Formatters\DateFormatter;
use TeamNiftyGmbH\DataTable\Formatters\FloatFormatter;
use TeamNiftyGmbH\DataTable\Formatters\FormatterRegistry;
use TeamNiftyGmbH\DataTable\Formatters\MoneyFormatter;
use TeamNiftyGmbH\DataTable\Formatters\PercentageFormatter;
use Throwable;

trait HasGeneratedWidgetConfig
{
    use Widgetable;

    // Not #[Locked] because $config is passed as a mount parameter from both the
    // dashboard grid (DB-stored widget config) and the wizard preview. Locking
    // would prevent Livewire from hydrating it via mount().
    // Server-side validation of the datatable class happens in buildFilteredQuery().
    public ?array $config = null;

    public bool $configError = false;

    public ?string $configErrorMessage = null;

    protected mixed $resolvedDataTable = null;

    public static function dashboardComponent(): array
    {
        return [];
    }

    public static function getCategory(): ?string
    {
        return 'generated';
    }

    public static function getLabel(): string
    {
        if (app()->runningInConsole()) {
            return Str::headline(class_basename(static::class));
        }

        return __(Str::headline(class_basename(static::class)));
    }

    protected function title(): ?string
    {
        return data_get($this->config, 'name') ?? static::getLabel();
    }

    protected function buildFilteredQuery(): ?Builder
    {
        $datatable = $this->getDataTableInstance();

        if (is_null($datatable)) {
            return null;
        }

        try {
            $userFilters = data_get($this->config, 'filters', []);

            return $datatable->buildWidgetQuery($userFilters);
        } catch (Throwable) {
            $this->configError = true;
            $this->configErrorMessage = __('Widget query could not be executed.');

            return null;
        }
    }

    protected function getDataTableInstance(): mixed
    {
        $datatableClass = data_get($this->config, 'datatable');

        if (! $datatableClass || ! is_string($datatableClass) || ! class_exists($datatableClass)) {
            $this->configError = true;
            $this->configErrorMessage = __('Invalid DataTable configuration.');

            return null;
        }

        if (! in_array(HasWidgetGeneration::class, class_uses_recursive($datatableClass))) {
            $this->configError = true;
            $this->configErrorMessage = __('DataTable does not support widget generation.');

            return null;
        }

        if (! isset($this->resolvedDataTable)) {
            try {
                $this->resolvedDataTable = Livewire::new($datatableClass);
            } catch (Throwable) {
                $this->configError = true;
                $this->configErrorMessage = __('Widget could not be initialized.');

                return null;
            }
        }

        return $this->resolvedDataTable;
    }

    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    protected function getAggregate(): ?string
    {
        return $this->getConfigValue('aggregate', 'count');
    }

    protected function getValueColumn(): ?string
    {
        return $this->getConfigValue('value_column');
    }

    protected function getGroupColumn(): ?string
    {
        return $this->getConfigValue('group_column');
    }

    protected function getDateColumn(): ?string
    {
        return $this->getConfigValue('date_column');
    }

    protected function isTimeframeAware(): bool
    {
        return (bool) $this->getConfigValue('timeframe_aware', false);
    }

    protected function validateColumnName(?string $column): ?string
    {
        if (is_null($column)) {
            return null;
        }

        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column)) {
            $this->configError = true;
            $this->configErrorMessage = __('Invalid column name in configuration.');

            return null;
        }

        return $column;
    }

    protected function formatColumnValue(string $column, mixed $value): string
    {
        $formatter = $this->resolveFormatter($column);

        if (is_null($formatter)) {
            return (string) $value;
        }

        try {
            return $formatter->format($value, []);
        } catch (Throwable) {
            return (string) $value;
        }
    }

    protected function resolveJsFormatterName(?string $column): string
    {
        if (! $column) {
            return 'float';
        }

        $formatter = $this->resolveFormatter($column);

        if (is_null($formatter)) {
            return 'float';
        }

        return match (true) {
            $formatter instanceof MoneyFormatter => 'money',
            $formatter instanceof PercentageFormatter => 'percentage',
            $formatter instanceof DateFormatter => match ($formatter->mode ?? 'date') {
                'datetime' => 'datetime',
                'relative' => 'relativeTime',
                'time' => 'datetime',
                default => 'date',
            },
            $formatter instanceof FloatFormatter => 'float',
            $formatter instanceof BooleanFormatter => 'boolean',
            default => 'string',
        };
    }

    protected function resolveFormatter(string $column): mixed
    {
        $datatable = $this->getDataTableInstance();
        $datatableClass = data_get($this->config, 'datatable');

        if (! $datatable || ! $datatableClass) {
            return null;
        }

        try {
            $registry = app(FormatterRegistry::class);
            $customFormatters = $datatable->getFormatters();
            $model = app($datatableClass::getWidgetModel());
            $modelCasts = $model->getCasts();
            $baseCol = str_contains($column, '.') ? last(explode('.', $column)) : $column;

            if (isset($customFormatters[$column]) && is_string($customFormatters[$column])) {
                return $registry->resolve($customFormatters[$column]);
            }

            if (isset($customFormatters[$column]) && is_array($customFormatters[$column])) {
                return $registry->resolveWithOptions(
                    $customFormatters[$column][0] ?? 'string',
                    $customFormatters[$column][1] ?? []
                );
            }

            $stringCasts = array_filter($modelCasts, 'is_string');

            return $registry->resolveForColumn($baseCol, $stringCasts);
        } catch (Throwable) {
            return null;
        }
    }

    protected function renderWithErrorCheck(View $defaultView): View
    {
        if ($this->configError) {
            return view('flux::livewire.widgets.generated-widget-error', [
                'message' => $this->configErrorMessage,
            ]);
        }

        return $defaultView;
    }
}
