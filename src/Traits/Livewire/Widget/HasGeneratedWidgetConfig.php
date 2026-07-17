<?php

namespace FluxErp\Traits\Livewire\Widget;

use FluxErp\Traits\Livewire\DataTable\HasWidgetGeneration;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;
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

    protected ?Component $resolvedDataTable = null;

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

    protected function buildAggregateExpression(string $aggregate, ?string $valueColumn): string
    {
        $column = ! is_null($valueColumn)
            ? '`' . str_replace('.', '`.`', $valueColumn) . '`'
            : null;

        return match ($aggregate) {
            'sum' => "SUM({$column}) as aggregate_value",
            'avg' => "AVG({$column}) as aggregate_value",
            'min' => "MIN({$column}) as aggregate_value",
            'max' => "MAX({$column}) as aggregate_value",
            default => 'COUNT(*) as aggregate_value',
        };
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

    protected function getAggregate(): ?string
    {
        return $this->getConfigValue('aggregate', 'count');
    }

    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    protected function getDataTableInstance(): ?Component
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

        if (is_null($this->resolvedDataTable)) {
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

    protected function getDateColumn(): ?string
    {
        return $this->getConfigValue('date_column');
    }

    protected function getGroupColumn(): ?string
    {
        return $this->getConfigValue('group_column');
    }

    protected function getValueColumn(): ?string
    {
        return $this->getConfigValue('value_column');
    }

    protected function isTimeframeAware(): bool
    {
        return (bool) $this->getConfigValue('timeframe_aware', false);
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

    protected function resolveFormatter(string $column): ?object
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
            $customFormatter = data_get($customFormatters, $column);

            if (is_string($customFormatter)) {
                return $registry->resolve($customFormatter);
            }

            if (is_array($customFormatter)) {
                return $registry->resolveWithOptions(
                    data_get($customFormatter, 0, 'string'),
                    data_get($customFormatter, 1, [])
                );
            }

            $stringCasts = array_filter($modelCasts, 'is_string');
            $resolvedCasts = array_map(
                function (string $cast): string {
                    $castClass = str_contains($cast, ':') ? substr($cast, 0, strpos($cast, ':')) : $cast;

                    if (class_exists($castClass) && is_subclass_of($castClass, HasFrontendFormatter::class)) {
                        $formatter = $castClass::getFrontendFormatter();

                        return is_string($formatter) ? $formatter : $cast;
                    }

                    return $cast;
                },
                $stringCasts
            );

            return $registry->resolveForColumn($baseCol, $resolvedCasts);
        } catch (Throwable) {
            return null;
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

    protected function resolveLabelsForGroup(string $column, iterable $values): array
    {
        $collected = collect($values)->filter(fn (mixed $value) => ! is_null($value))->unique()->values();

        if (! str_ends_with($column, '_id') || $column === 'id') {
            return $collected
                ->mapWithKeys(fn (mixed $value) => [(string) $value => strip_tags($this->formatColumnValue($column, $value))])
                ->all();
        }

        $datatable = $this->getDataTableInstance();

        if (is_null($datatable)) {
            return $collected
                ->mapWithKeys(fn (mixed $value) => [(string) $value => (string) $value])
                ->all();
        }

        try {
            $model = app($datatable::getWidgetModel());
            $relationName = Str::camel(Str::beforeLast($column, '_id'));

            if (! method_exists($model, $relationName)) {
                return $collected
                    ->mapWithKeys(fn (mixed $value) => [(string) $value => strip_tags($this->formatColumnValue($column, $value))])
                    ->all();
            }

            $relation = $model->{$relationName}();
            $related = $relation->getRelated();
            $ownerKey = method_exists($relation, 'getOwnerKeyName') ? $relation->getOwnerKeyName() : $related->getKeyName();

            $records = $related->newQuery()
                ->whereIn($ownerKey, $collected->all())
                ->get()
                ->keyBy($ownerKey);

            return $collected
                ->mapWithKeys(function (mixed $value) use ($records) {
                    $record = $records->get($value);

                    if (! $record) {
                        return [(string) $value => (string) $value];
                    }

                    $label = $this->resolveRelatedRecordLabel($record);

                    return [(string) $value => (string) ($label ?? $record->getKey())];
                })
                ->all();
        } catch (Throwable) {
            return $collected
                ->mapWithKeys(fn (mixed $value) => [(string) $value => (string) $value])
                ->all();
        }
    }

    protected function resolveRelatedRecordLabel(mixed $record): ?string
    {
        foreach (['detailLabel', 'getLabel'] as $method) {
            if (method_exists($record, $method)) {
                $label = $record->{$method}();

                if (! blank($label)) {
                    return (string) $label;
                }
            }
        }

        foreach (['name', 'label', 'title'] as $attribute) {
            $value = data_get($record, $attribute);

            if (! blank($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    protected function title(): ?string
    {
        return data_get($this->config, 'name') ?? static::getLabel();
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
}
