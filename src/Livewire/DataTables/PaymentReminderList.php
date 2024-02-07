<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentReminder;
use TeamNiftyGmbH\DataTable\DataTable;

class PaymentReminderList extends DataTable
{
    protected string $model = PaymentReminder::class;

    public array $enabledCols = [
        'order.invoice_number',
        'reminder_level',
    ];
}
