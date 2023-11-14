<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\MailFolder;
use TeamNiftyGmbH\DataTable\DataTable;

class MailFolderList extends DataTable
{
    protected string $model = MailFolder::class;

    public array $enabledCols = [
        'name',
        'slug',
    ];
}
