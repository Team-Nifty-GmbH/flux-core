<?php

namespace FluxErp\Jobs;

use FluxErp\Notifications\ExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Facades\Excel;
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
        $query = invade(unserialize($this->component))->buildSearch();

        $fileName = morph_alias($this->modelClass) . '_' . now()->toDateTimeLocalString('minute') . '.xlsx';
        $folder = 'exports/' . str_replace(':', '_', $this->userMorph) . '/';
        $filePath = $folder . str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $fileName);

        Excel::store(
            app(
                DataTableExport::class,
                [
                    'builder' => $query,
                    'exportColumns' => $this->columns,
                ]
            ),
            $filePath
        );

        $user = morph_to($this->userMorph);
        $user->notify(ExportReady::make($filePath, morph_alias($this->modelClass)));
    }
}
