<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\MailFolder;

class MailFolderList extends BaseDataTable
{
    protected string $model = MailFolder::class;

    public array $enabledCols = [
        'name',
        'slug',
    ];
}
