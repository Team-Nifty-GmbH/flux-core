<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentReminderText;

class PaymentReminderTextList extends BaseDataTable
{
    public array $enabledCols = [
        'reminder_level',
        'reminder_subject',
    ];

    protected string $model = PaymentReminderText::class;
}
