<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PaymentReminderText;

class PaymentReminderTextList extends BaseDataTable
{
    protected string $model = PaymentReminderText::class;

    public array $enabledCols = [
        'reminder_level',
        'reminder_subject',
    ];
}
