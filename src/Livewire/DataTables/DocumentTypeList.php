<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\DocumentType;

class DocumentTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'retention_years',
        'is_owner_uploadable',
        'is_active',
    ];

    protected string $model = DocumentType::class;
}
