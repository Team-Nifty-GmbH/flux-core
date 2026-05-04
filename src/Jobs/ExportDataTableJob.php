<?php

namespace FluxErp\Jobs;

use FluxErp\Notifications\ExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use TeamNiftyGmbH\DataTable\Exports\CsvExport;
use TeamNiftyGmbH\DataTable\Exports\DataTableExport;
use TeamNiftyGmbH\DataTable\Exports\JsonExport;
use function Livewire\invade;

class ExportDataTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected string $component,
        protected string $modelClass,
        protected array $columns,
        protected string $userMorph,
        protected string $format = 'xlsx',
        protected bool $formatted = true,
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
                'format' => $this->format,
                'formatted' => $this->formatted,
            ])
            ->useLog('export')
            ->event('export_started')
            ->log(morph_alias($this->modelClass) . ' export started');

        $component = unserialize($this->component);
        $query = invade($component)->buildSearch();

        [$exportClass, $extension] = match ($this->format) {
            'csv' => [CsvExport::class, '.csv'],
            'json' => [JsonExport::class, '.json'],
            default => [DataTableExport::class, '.xlsx'],
        };

        $fileName = morph_alias($this->modelClass) . '_' . now()->toDateTimeLocalString('minute') . $extension;
        $folder = 'exports/' . str_replace(':', '_', $this->userMorph) . '/';
        $filePath = $folder . str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $fileName);

        $formatters = $this->formatted
            ? invade($component)->resolveExportFormatters(app($this->modelClass), $this->columns)
            : [];

        app(
            $exportClass,
            [
                'builder' => $query,
                'exportColumns' => $this->columns,
                'formatters' => $formatters,
            ]
        )
            ->store($filePath);

        $user->notify(ExportReady::make($filePath, morph_alias($this->modelClass)));
    }
}
