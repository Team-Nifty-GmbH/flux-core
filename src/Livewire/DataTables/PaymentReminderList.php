<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentReminder;

class PaymentReminderList extends BaseDataTable
{
    protected string $model = PaymentReminder::class;

    public array $enabledCols = [
        'order.invoice_number',
        'reminder_level',
        'created_at',
    ];
}
