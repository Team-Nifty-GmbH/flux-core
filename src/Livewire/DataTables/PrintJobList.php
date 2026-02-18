<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PrintJob;
use Illuminate\Database\Eloquent\Builder;

class PrintJobList extends BaseDataTable
{
    public array $enabledCols = [
        'user.name',
        'media.name',
        'media.disk',
        'printer.name',
        'quantity',
        'size',
        'status',
        'is_completed',
    ];

    protected string $model = PrintJob::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('media');
    }
}
