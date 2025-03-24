<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\LanguageLine;

class LanguageLineList extends BaseDataTable
{
    public array $enabledCols = [
        'group',
        'key',
        'text',
    ];

    protected string $model = LanguageLine::class;
}
