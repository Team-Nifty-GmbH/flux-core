<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\BlockedFile;

class BlockedFileList extends BaseDataTable
{
    public array $enabledCols = [
        'file_name',
        'mime_type',
        'size',
        'created_by',
        'created_at',
    ];

    protected string $model = BlockedFile::class;
}
