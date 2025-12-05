<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Tenant;

class TenantList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'tenant_code',
        'country.name',
        'postcode',
        'city',
        'phone',
    ];

    protected string $model = Tenant::class;
}
