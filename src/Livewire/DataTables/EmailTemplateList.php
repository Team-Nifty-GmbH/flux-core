<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\EmailTemplate;

class EmailTemplateList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'subject',
    ];

    protected string $model = EmailTemplate::class;
}
