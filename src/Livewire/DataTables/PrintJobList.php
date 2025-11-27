<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PrintJob;
use Illuminate\Database\Eloquent\Builder;

class PrintJobList extends BaseDataTable
{
    public array $enabledCols = [
        'user.name',
        'media.name',
        'printer.name',
        'quantity',
        'size',
        'is_completed',
    ];

    protected string $model = PrintJob::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with('media:id,model_type,model_id,name,disk');
    }
}
