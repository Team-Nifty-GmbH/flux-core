<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentType;
use TeamNiftyGmbH\DataTable\DataTable;

class PaymentTypeList extends DataTable
{
    protected string $model = PaymentType::class;

    public array $enabledCols = [
        'name',
        'payment_reminder_days_1',
        'payment_reminder_days_2',
        'payment_reminder_days_3',
        'is_active',
    ];
}
