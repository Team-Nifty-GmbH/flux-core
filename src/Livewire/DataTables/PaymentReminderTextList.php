<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentReminderText;
use TeamNiftyGmbH\DataTable\DataTable;

class PaymentReminderTextList extends DataTable
{
    protected string $model = PaymentReminderText::class;

    public array $enabledCols = [
        'reminder_level',
        'reminder_subject',
    ];
}
