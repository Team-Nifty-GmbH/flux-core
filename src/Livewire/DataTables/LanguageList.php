<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Language;

class LanguageList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'iso_name',
        'language_code',
    ];

    protected string $model = Language::class;
}
