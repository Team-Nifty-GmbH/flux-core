<?php

namespace FluxErp\Jobs;

use FluxErp\Contracts\ShouldBeMonitored;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Traits\IsMonitored;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Exports\CsvExport;
use TeamNiftyGmbH\DataTable\Exports\DataTableExport;
use TeamNiftyGmbH\DataTable\Exports\JsonExport;
use function Livewire\invade;

class ExportDataTableJob implements ShouldBeMonitored, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable;

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

    public function getName(): string
    {
        return __(':model export', [
            'model' => __(Str::headline(morph_alias($this->modelClass))),
        ]);
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
            'xlsx' => [DataTableExport::class, '.xlsx'],
        };

        $fileName = morph_alias($this->modelClass) . '_' . now()->toDateTimeLocalString('minute') . $extension;
        $folder = 'exports/' . str_replace(':', '_', $this->userMorph) . '/';
        $filePath = $folder . str_replace(['<', '>', ':', '"', '/', '\\', '|', '?', '*'], '_', $fileName);

        $formatters = $this->formatted
            ? invade($component)->resolveExportFormatters(app($this->modelClass), $this->columns)
            : [];

        $total = (clone $query)->toBase()->getCountForPagination();
        $this->message(__(':count :model rows', [
            'count' => $total,
            'model' => __(Str::plural(Str::headline(morph_alias($this->modelClass)))),
        ]));

        app(
            $exportClass,
            [
                'builder' => $query,
                'exportColumns' => $this->columns,
                'formatters' => $formatters,
                'onChunk' => $total > 0
                    ? fn (int $processed) => $this->queueProgress(min(99, (int) ($processed / $total * 100)))
                    : null,
            ]
        )
            ->store($filePath);

        $this->accept(
            NotificationAction::make()
                ->label(__('Download'))
                ->url(route('private-storage', ['path' => $filePath]))
                ->download()
        );
    }
}
