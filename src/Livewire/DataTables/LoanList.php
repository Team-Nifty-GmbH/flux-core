<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Loan;
use Illuminate\Database\Eloquent\Builder;

class LoanList extends BaseDataTable
{
    public array $columnLabels = [
        'contact.invoice_address.name' => 'Contact',
    ];

    public array $enabledCols = [
        'name',
        'contact.invoice_address.name',
        'amount',
        'remaining',
        'total_interest',
        'progress',
        'number_of_installments',
        'starts_at',
    ];

    public array $formatters = [
        'amount' => 'coloredMoney',
        'interest_rate' => 'percentage',
        'progress' => 'progressPercentage',
        'remaining' => 'coloredMoney',
        'total_interest' => 'coloredMoney',
        'starts_at' => 'date',
    ];

    public array $sortable = [
        'name',
        'amount',
        'progress',
        'remaining',
        'total_interest',
        'starts_at',
    ];

    protected string $model = Loan::class;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('contact.invoiceAddress:id,contact_id,name');
    }
}
