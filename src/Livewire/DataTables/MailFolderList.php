<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\MailFolder;

class MailFolderList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'slug',
    ];

    protected string $model = MailFolder::class;
}
