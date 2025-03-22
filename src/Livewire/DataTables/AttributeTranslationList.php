<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AttributeTranslation;

class AttributeTranslationList extends BaseDataTable
{
    public array $enabledCols = [
        'language.name',
        'value',
        'model_id',
        'model_type',
    ];

    protected string $model = AttributeTranslation::class;
}
