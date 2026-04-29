<?php

namespace FluxErp\Jobs;

use FluxErp\Notifications\ExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use TeamNiftyGmbH\DataTable\Exports\DataTableExport;
use function Livewire\invade;

class ExportDataTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected string $component,
        protected string $modelClass,
        protected array $columns,
        protected string $userMorph
    ) {
        $this->columns = array_filter($columns);
    }

    public function handle(): void
    {
        $user = morph_to($this->userMorph);

        activity()
            ->causedBy($user)
            ->withProperties([
                'model' => $this->modelClass,
                'columns' => $this->columns,
            ])
            ->useLog('export')
            ->event('export_started')
            ->log(morph_alias($this->modelClass) . ' export started');

        $query = invade(unserialize($this->component))->buildSearch();

        $fileName = morph_alias($this->modelClass) . '_' . now()->toDateTimeLocalString('minute') . '.xlsx';
        $folder = 'exports/' . str_replace(':', '_', $this->userMorph) . '/';
        $filePath = $folder . str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $fileName);

        app(
            DataTableExport::class,
            [
                'builder' => $query,
                'exportColumns' => $this->columns,
            ]
        )->store($filePath);

        $user->notify(ExportReady::make($filePath, morph_alias($this->modelClass)));
    }
}
