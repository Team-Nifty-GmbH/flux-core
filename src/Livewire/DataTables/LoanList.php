<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Loan;
use Illuminate\Database\Eloquent\Builder;

class LoanList extends BaseDataTable
{
    public array $columnLabels = [
        'contact.invoice_address.name' => 'Contact',
        'overdue_installments_count' => 'Overdue Installments',
    ];

    public array $enabledCols = [
        'name',
        'contact.invoice_address.name',
        'amount',
        'remaining',
        'total_interest',
        'progress',
        'overdue_installments_count',
        'number_of_installments',
        'starts_at',
    ];

    public array $formatters = [
        'amount' => 'coloredMoney',
        'interest_rate' => 'percentage',
        'remaining' => 'coloredMoney',
        'total_interest' => 'coloredMoney',
        'progress' => 'progressPercentage',
        'starts_at' => 'date',
    ];

    public array $sortable = [
        'name',
        'amount',
        'remaining',
        'total_interest',
        'progress',
        'starts_at',
    ];

    protected string $model = Loan::class;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->with('contact.invoiceAddress:id,contact_id,name')
            ->withCount([
                'installments as overdue_installments_count' => fn (Builder $query) => $query->overdue(),
            ]);
    }
}
