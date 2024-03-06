<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Language;

class LanguageList extends BaseDataTable
{
    protected string $model = Language::class;

    public array $enabledCols = [
        'name',
        'iso_name',
        'language_code',
    ];
}
