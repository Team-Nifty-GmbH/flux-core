<?php

namespace FluxErp\Tests\Unit\Livewire\DataTable;

use FluxErp\Livewire\DataTables\BaseDataTable;

class ExportTestDataTable extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'tenant_code',
    ];

    protected string $model = \FluxErp\Models\Tenant::class;
}
