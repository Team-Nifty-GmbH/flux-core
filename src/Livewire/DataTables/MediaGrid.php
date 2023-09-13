<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Media;
use TeamNiftyGmbH\DataTable\DataTable;

class MediaGrid extends DataTable
{
    protected string $model = Media::class;

    public array $enabledCols = [
        'original_url',
        'file_name',
    ];

    public array $formatters = [
        'original_url' => 'image',
    ];

    public function getLayout(): string
    {
        return 'tall-datatables::layouts.grid';
    }
}
