<?php

namespace FluxErp\Jobs;

use FluxErp\Notifications\ExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Facades\Excel;
use TeamNiftyGmbH\DataTable\Exports\DataTableExport;

class ExportDataTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected string $sql,
        protected array $bindings,
        protected string $modelClass,
        protected array $columns,
        protected string $userMorph
    ) {
        $this->columns = array_filter($columns);
    }

    public function handle(): void
    {
        /** @var Model $model */
        $model = new $this->modelClass();

        $query = $model->newModelQuery()
            ->fromRaw($this->sql, $this->bindings)
            ->from($model->getTable());

        $fileName = morph_alias($this->modelClass) . '_' . now()->toDateTimeLocalString('minute') . '.xlsx';
        $filePath = 'exports/' . str_replace(':', '_', $this->userMorph) . '/' . $fileName;

        Excel::store(
            new DataTableExport($query, $this->columns),
            $filePath
        );

        $user = morph_to($this->userMorph);
        $user->notify(ExportReady::make($filePath, morph_alias($this->modelClass)));
    }
}
