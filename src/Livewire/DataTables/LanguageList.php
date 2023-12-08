<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Language;
use TeamNiftyGmbH\DataTable\DataTable;

class LanguageList extends DataTable
{
    protected string $model = Language::class;

    public array $enabledCols = [
        'name',
        'iso_name',
        'language_code',
    ];
}
