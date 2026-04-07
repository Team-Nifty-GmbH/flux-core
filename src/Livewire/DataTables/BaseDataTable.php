<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\DataTable\ShareFilter;
use FluxErp\Jobs\ExportDataTableJob;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Http\Response;
use Livewire\Attributes\Renderless;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

abstract class BaseDataTable extends DataTable
{
    use Actions, HasEloquentListeners;

    #[Renderless]
    public function export(array $columns = []): Response|BinaryFileResponse
    {
        ExportDataTableJob::dispatch(
            serialize($this),
            $this->getModel(),
            $columns,
            auth()->user()->getMorphClass() . ':' . auth()->id()
        );

        $this->toast()
            ->success(
                __('Export started'),
                __('Your export is being processed. You will be notified when it is ready.')
            )
            ->send();

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
