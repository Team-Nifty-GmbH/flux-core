<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Language;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class LanguageList extends DataTable
{
    protected string $model = Language::class;

    public array $enabledCols = [
        'name',
        'iso_name',
        'language_code',
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
