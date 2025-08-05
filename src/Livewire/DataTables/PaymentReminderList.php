<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentReminder;

class PaymentReminderList extends BaseDataTable
{
    public array $enabledCols = [
        'order.invoice_number',
        'reminder_level',
        'created_at',
    ];

    protected string $model = PaymentReminder::class;
}
