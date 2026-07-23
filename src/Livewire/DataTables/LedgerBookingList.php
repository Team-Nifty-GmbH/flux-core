<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\LedgerBooking;
use Illuminate\Database\Eloquent\Builder;

class LedgerBookingList extends BaseDataTable
{
    public array $columnLabels = [
        'debitLedgerAccount.name' => 'Debit Account',
        'creditLedgerAccount.name' => 'Credit Account',
    ];

    public array $enabledCols = [
        'booking_date',
        'debitLedgerAccount.name',
        'creditLedgerAccount.name',
        'amount',
        'booking_text',
    ];

    public array $formatters = [
        'booking_date' => 'date',
        'amount' => 'money',
    ];

    public array $sortable = [
        'booking_date',
        'amount',
    ];

    protected string $model = LedgerBooking::class;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with(['debitLedgerAccount:id,name,number', 'creditLedgerAccount:id,name,number']);
    }
}
