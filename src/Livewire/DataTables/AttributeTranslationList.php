<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AttributeTranslation;

class AttributeTranslationList extends BaseDataTable
{
    protected string $model = AttributeTranslation::class;

    public array $enabledCols = [
        'language.name',
        'value',
        'model_id',
        'model_type',
    ];
}
