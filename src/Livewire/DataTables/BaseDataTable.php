<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\DataTable\ShareFilter;
use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTable\HasWidgetGeneration;
use Illuminate\Http\Response;
use Livewire\Attributes\Renderless;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

abstract class BaseDataTable extends DataTable
{
    use Actions, HasEloquentListeners, HasWidgetGeneration;

    #[Renderless]
    public function export(array $columns = [], string $format = 'xlsx', bool $formatted = true): Response|BinaryFileResponse|StreamedResponse
    {
        $columns = array_filter($columns) ?: $this->enabledCols;

        ExportDataTableJob::dispatch(
            serialize($this),
            $this->getModel(),
            $columns,
            auth()->user()->getMorphClass() . ':' . auth()->id(),
            $format,
            $formatted,
        );

        return response()->noContent();
    }

    protected function getDisplayTimezone(): string
    {
        return data_get(auth()->user(), 'timezone')
            ?? config('flux.display_timezone')
            ?? config('app.timezone');
    }

    protected function getModel(): string
    {
        return resolve_static($this->model, 'class');
    }

    protected function canSaveDefaultColumns(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    protected function canShareFilters(): bool
    {
        return resolve_static(ShareFilter::class, 'canPerformAction', [false]);
    }
}
