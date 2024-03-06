<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentType;

class PaymentTypeList extends BaseDataTable
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
