<?php

namespace FluxErp\Traits\Livewire\Widget;

use FluxErp\Traits\Livewire\DataTable\HasWidgetGeneration;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Formatters\FormatterRegistry;
use Throwable;

trait HasGeneratedWidgetConfig
{
    use Widgetable;

    #[Locked]
    public ?array $config = null;

    public bool $configError = false;

    public ?string $configErrorMessage = null;

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
        $datatableClass = data_get($this->config, 'datatable');

        if (! $datatableClass || ! class_exists($datatableClass)) {
            $this->configError = true;
            $this->configErrorMessage = __('Invalid DataTable configuration.');

            return null;
        }

        if (! in_array(HasWidgetGeneration::class, class_uses_recursive($datatableClass))) {
            $this->configError = true;
            $this->configErrorMessage = __('DataTable does not support widget generation.');

            return null;
        }

        try {
            $datatable = Livewire::new($datatableClass);
            $userFilters = data_get($this->config, 'filters', []);

            return $datatable->buildWidgetQuery($userFilters);
        } catch (Throwable $e) {
            $this->configError = true;
            $this->configErrorMessage = $e->getMessage();

            return null;
        }
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

        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $column)) {
            $this->configError = true;
            $this->configErrorMessage = __('Invalid column name in configuration.');

            return null;
        }

        return $column;
    }

    protected function formatColumnValue(string $column, mixed $value): string
    {
        $datatableClass = data_get($this->config, 'datatable');

        if (! $datatableClass || ! class_exists($datatableClass)) {
            return (string) $value;
        }

        try {
            $datatable = Livewire::new($datatableClass);
            $registry = app(FormatterRegistry::class);
            $customFormatters = $datatable->getFormatters();
            $model = app($datatable->getModel());
            $modelCasts = $model->getCasts();

            $baseCol = str_contains($column, '.') ? last(explode('.', $column)) : $column;

            if (isset($customFormatters[$column]) && is_string($customFormatters[$column])) {
                $formatter = $registry->resolve($customFormatters[$column]);
            } elseif (isset($customFormatters[$column]) && is_array($customFormatters[$column])) {
                $formatter = $registry->resolveWithOptions($customFormatters[$column][0] ?? 'string', $customFormatters[$column][1] ?? []);
            } else {
                $stringCasts = array_filter($modelCasts, 'is_string');
                $formatter = $registry->resolveForColumn($baseCol, $stringCasts);
            }

            return $formatter->format($value, []);
        } catch (Throwable) {
            return (string) $value;
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
